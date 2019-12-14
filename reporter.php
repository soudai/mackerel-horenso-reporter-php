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

function get_execution_time($report)
{
    $start_at = new DateTime($report->startAt);
    $end_at = new DateTime($report->endAt);
    $execution_time = $end_at->getTimestamp() - $start_at->getTimestamp();
    return $execution_time;
}

function make_annotation($mackerel_service_name, $batch_name, $execution_time, $report)
{
    $annotation = new \Mackerel\GraphAnnotation();

    $start_at = new DateTime($report->startAt);
    $start_at_string = $start_at->setTimezone(new DateTimeZone('Asia/Tokyo'))
        ->format('Y-m-d H:i:s');

    $end_at = new DateTime($report->endAt);
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

