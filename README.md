Service API
===========

[![Latest Stable Version](https://poser.pugx.org/matryoshka-model/service-api/v/stable.png)](https://packagist.org/packages/matryoshka-model/service-api) [![Build Status](https://travis-ci.org/matryoshka-model/service-api.svg?branch=master)](https://travis-ci.org/matryoshka-model/service-api) [![Coverage Status](https://coveralls.io/repos/matryoshka-model/service-api/badge.png?branch=master)](https://coveralls.io/r/matryoshka-model/service-api?branch=master) [![Dependency Status](https://www.versioneye.com/user/projects/5453c98322b4fbeff00000c2/badge.svg)](https://www.versioneye.com/user/projects/5453c98322b4fbeff00000c2)

A set of utilities aimed at consuming HTTP API services.

## Installation

Install it using [composer](http://getcomposer.org).

Add the following to your `composer.json` file:

```
"require": {
    "php": ">=5.4",
    "matryoshka-model/service-api": "~0.5.0"
}
```

## Configuration

This library provides two factories for `Zend\ServiceManager` to make Zend\Http\Client and Matryoshka\Service\Api\Client\HttpApi available as services. In order to use them in a ZF2 application, register the provided factories through the `service_manager` configuration node:

```php
'service_manager'    => [
    'factories' => [
        'Matryoshka\Service\Api\Client\HttpClient' => 'Matryoshka\Service\Api\Service\HttpClientServiceFactory',
    ],
    'abstract_factories' => [
        'Matryoshka\Service\Api\Service\HttpApiAbstractServiceFactory',
    ],
],
```

Then in your configuration you can add the `matryoshka-httpclient` and `matryoshka-service-api` nodes and configure them as in example:

```php
'matryoshka-httpclient' => [
    'uri'       => 'http://example.net/path', //base uri
    ... //any other options available for Zend\Http\Client
],

'matryoshka-service-api'    => [
    'YourApiServiceName' => [
        'http_client'       => 'Matryoshka\Service\Api\Client\HttpClient', // http client service name
        'base_request'      => 'Zend\Http\Request',                        // base request service name
        'valid_status_code  => [],                                         // Array of int code valid
        'request_format'    => 'json',                                     // string json/xml
        'profiler'          => '',                                         // profiler service name
    ],
    ...
],
```

---

[![Analytics](https://ga-beacon.appspot.com/UA-49655829-1/matryoshka-model/service-api)](https://github.com/igrigorik/ga-beacon)
