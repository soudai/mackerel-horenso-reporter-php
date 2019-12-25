<?php

require __DIR__ . '/vendor/autoload.php';

$mackerel_api_key = '***********';
$mackerel_service_name = 'Mackerel Server name';

$report = get_horenso_report();
$execution_time = get_execution_time($report);

// horenso -t {tag_name} or Change $batch_name freely
$batch_name = $report->tag;

$client = new \Mackerel\Client([
    'mackerel_api_key' => $mackerel_api_key,
]);

$post_time = time();

$metrics = [
    [
        'name' => "{$mackerel_service_name}.{$batch_name}.execution_time",
        'time' => $post_time,
        'value' => $execution_time,
    ]
];

$client->postServiceMetrics($mackerel_service_name, $metrics);

$annotation = make_annotation($mackerel_service_name, $batch_name, $execution_time, $report);

$client->postGraphAnnotation($annotation);

// end

function get_horenso_report()
{
    $json = stream_get_contents(STDIN);
    return json_decode($json);
}

/**
 * PHPのDatetimeは小数点8桁までしかサポートしていない
 * そのため Songmu/horenso のdatetimeを受け取れないことがある
 * よって8桁に合わせる
 *
 * https://github.com/Songmu/horenso/blob/a83321ce21d2d779505a7375acb664f15d687d2a/horenso.go#L331
 *
 * > when turning time.Time to JSON with json.Marshal()).
 * > Currently there is no other solution than using a separate parsing library to get correct dates.
 *
 * https://www.php.net/manual/ja/datetime.createfromformat.php
 */
function get_datetime_for_php($datetime_ISO8601_ns)
{
    $pattern = '/^(\\d{4}-\\d{2}-\\d{2}T\\d{2}:\\d{2}:\\d{2})(\\.\\d{8})\\d*(.*)/';
    return preg_replace($pattern, '$1$2$3', $datetime_ISO8601_ns);
}

function get_execution_time($report)
{
    $start_at = new DateTime(get_datetime_for_php($report->startAt));
    $end_at = new DateTime(get_datetime_for_php($report->endAt));
    $execution_time = $end_at->getTimestamp() - $start_at->getTimestamp();
    return $execution_time;
}

function make_annotation($mackerel_service_name, $batch_name, $execution_time, $report)
{
    $annotation = new \Mackerel\Objects\GraphAnnotation();

    $start_at = new DateTime(get_datetime_for_php($report->startAt));
    $start_at_string = $start_at->setTimezone(new DateTimeZone('Asia/Tokyo'))
        ->format('Y-m-d H:i:s');

    $end_at = new DateTime(get_datetime_for_php($report->endAt));
    $end_at_string = $end_at->setTimezone(new DateTimeZone('Asia/Tokyo'))
        ->format('Y-m-d H:i:s');

    $description = <<<EOF
実行開始 : $start_at_string
実行終了 : $end_at_string
実行時間 : $execution_time 秒 
EOF;

    $annotation->service = $mackerel_service_name;
    $annotation->title = $batch_name;
    $annotation->description = $description;
    $annotation->from = $start_at->getTimestamp();
    $annotation->to = $end_at->getTimestamp();

    return $annotation;
}

