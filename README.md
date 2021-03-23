# The worstpractice.dev presents

## A simple adapter for the AWS S3 Client

[![PHP Version](https://img.shields.io/badge/PHP-8.0-blue)](https://www.php.net/ChangeLog-8.php#8.0.3)
[![Build Status](https://scrutinizer-ci.com/g/Gixx/worstpractice-aws-s3-adapter/badges/build.png?b=master)](https://scrutinizer-ci.com/g/Gixx/worstpractice-aws-s3-adapter/build-status/master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Gixx/worstpractice-aws-s3-adapter/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Gixx/worstpractice-aws-s3-adapter/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/Gixx/worstpractice-aws-s3-adapter/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/Gixx/worstpractice-aws-s3-adapter/?branch=master)
[![Packagist Package](https://flat.badgen.net/packagist/name/gixx/worstpractice-aws-s3-adapter)](https://packagist.org/packages/gixx/worstpractice-aws-s3-adapter)
[![Packagist Downloads](https://flat.badgen.net/packagist/dt/gixx/worstpractice-aws-s3-adapter)](https://packagist.org/packages/gixx/worstpractice-aws-s3-adapter)

### Installation

To add this package to your project, just get it via composer:

```
composer require gixx/worstpractice-aws-s3-adapter
```

### Usage

To use it, you will need only a configuration as in the example:

```php

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
