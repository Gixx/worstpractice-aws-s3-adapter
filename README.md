# The worstpractice.dev presents

## A simple adapter for the AWS S3 Client

[![PHP Version](https://img.shields.io/badge/PHP-8.0-blue)](https://www.php.net/ChangeLog-8.php#8.0.3)
[![Build Status](https://scrutinizer-ci.com/g/Gixx/worstpractice-aws-s3-adapter/badges/build.png?b=main)](https://scrutinizer-ci.com/g/Gixx/worstpractice-aws-s3-adapter/build-status/main)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Gixx/worstpractice-aws-s3-adapter/badges/quality-score.png?b=main)](https://scrutinizer-ci.com/g/Gixx/worstpractice-aws-s3-adapter/?branch=main)
[![Code Coverage](https://scrutinizer-ci.com/g/Gixx/worstpractice-aws-s3-adapter/badges/coverage.png?b=main)](https://scrutinizer-ci.com/g/Gixx/worstpractice-aws-s3-adapter/?branch=main)
[![Packagist Package](https://flat.badgen.net/packagist/name/gixx/worstpractice-aws-s3-adapter)](https://packagist.org/packages/gixx/worstpractice-aws-s3-adapter)
[![Packagist Downloads](https://flat.badgen.net/packagist/dt/gixx/worstpractice-aws-s3-adapter)](https://packagist.org/packages/gixx/worstpractice-aws-s3-adapter)

### Installation

To add this package to your project, just get it via composer:

```
composer require gixx/worstpractice-aws-s3-adapter
```

### Usage

```php
<?php

defined('S3_ACCESS_KEY') || define('S3_ACCESS_KEY', '<MyAccessKey>');
defined('S3_SECRET_KEY') || define('S3_SECRET_KEY', '<MySecretKey>');

require_once('vendor/autoload.php');

use Aws\Credentials\Credentials;
use Aws\S3\S3Client;
use WorstPractice\Component\Aws\S3\Adapter;

$credentials = new Credentials(
    S3_ACCESS_KEY,
    S3_SECRET_KEY
);

$s3Client = new S3Client([
    'credentials' => $credentials,
    'region' => 'eu-central-1',
    'version' => '2006-03-01',
]);

$s3Adapter = new Adapter($s3Client);
$s3Adapter->setBucket('my-bucket');

// The result will be the "key" (kind of absolute path + filename) of the file in the S3 bucket of NULL when no file
// found on the given "path".
$latestFileInFolder = $s3Adapter->getLastUploadedKeyByPrefix('folder/subfolder');

```

### Testing

The package contains a simple Docker setup to be able to run tests. For this you need only run the following:
```
docker-compose up -d
docker exec -it worstpractice-aws-s3-adapter php -d memory_limit=-1 composer.phar install
docker exec -it worstpractice-aws-s3-adapter php composer.phar check
```

The following tests will run:
* PHP lint
* PHP Mess Detector
* PHP-CS-Fixer
* PHP Code Sniffer
* PHP Unit
* PHPStan
