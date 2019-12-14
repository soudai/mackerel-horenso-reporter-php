# mackerel-horenso-reporter-php

[horenso](https://github.com/Songmu/horenso/blob/master/horenso.go)を使ったBatchの監視時に、結果をMackerelのサービスメトリックとグラフアノテーションに投稿する。

# Install

```shell script
$ git clone https://github.com/soudai/mackerel-horenso-reporter-php.git

$ cd mackerel-horenso-reporter-php
$ composer install

// bash: composer: コマンドが見つかりません が出た場合
$ php -r "readfile('https://getcomposer.org/installer');" | php
$ mv composer.phar /usr/local/bin/composer
$ /usr/local/bin/composer install
```

# Setting

`$ vim reporter.php`

- $mackerel_api_key;
  - [MackerelのAPI Keyを確認](https://mackerel.io/my?tab=apikeys)
- $mackerel_service_name;
- $batch_name
  - horenso -t {tag_name} を指定しない場合は設定してください
  
# Use
horensoのreporterにmackerel-horenso-reporter-phpを指定して実行する

```shell script
$ /{path}/horenso -t {batch_name} \ 
  -r '/{path}/php /{path}/mackerel-horenso-reporter-php/reporter.php' \ 
  -- '/{path}/to/job args...'
```

# blog
- [horensoというcronやコマンドラッパー用のツールを書いた](https://songmu.jp/riji/entry/2016-01-05-horenso.html) @songmu
- [Batchの監視 ~ mkr wrapとhorensoを使いこなす](https://soudai.hatenablog.com/entry/2019/12/14/214337)
