<?php
/**
 * Matryoshka Service API
 *
 * @link        https://github.com/matryoshka-model/service-api
 * @copyright   Copyright (c) 2014, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace Matryoshka\Service\Api\Service;

use Zend\Http\Client;
use Zend\Http\Headers;
use Zend\Http\Request;
use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Matryoshka\Service\Api\Client\HttpApi;

/**
 * Class HttpApiAbstractServiceFactory
 */
class HttpApiAbstractServiceFactory implements AbstractFactoryInterface
{
    /**
     * @var string
     */
    protected $configKey = 'matryoshka-service-api';

    /**
     * @var array
     */
    protected $config;

    /**
     * Determine if we can create a service with name
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @param $name
     * @param $requestedName
     * @return bool
     */
    public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        $config = $this->getConfig($serviceLocator);
        if (empty($config)) {
            return false;
        }

        if (isset($config[$requestedName])
            && is_array($config[$requestedName])
            && !empty($config[$requestedName])
        ) {
            return true;
        }
        return false;
    }

    /**
     * Create service with name
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @param $name
     * @param $requestedName
     * @return HttpApi
     */
    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        $config = $this->getConfig($serviceLocator)[$requestedName];

        $httpClient = isset($config['http_client']) && $serviceLocator->has($config['http_client']) ?
        $serviceLocator->get($config['http_client']) : null;

        $baseRequest = isset($config['base_request']) && $serviceLocator->has($config['base_request']) ?
        $serviceLocator->get($config['base_request']) : null;

        $api = new HttpApi($httpClient, $baseRequest);

        // Array of int code valid
        if (isset($config['valid_status_code']) && is_array($config['valid_status_code'])) {
            $api->setValidStatusCodes($config['valid_status_code']);
        }
        // string json/xml
        if (isset($config['request_format'])) {
            $api->setRequestFormat($config['request_format']);
        }
        // Profiler
        if (isset($config['profiler']) && $serviceLocator->has($config['profiler'])) {
            $api->setProfiler($serviceLocator->get($config['profiler']));
        }

        return $api;
    }

    /**
     * Get rest configuration, if any
     *
     * @param  ServiceLocatorInterface $serviceLocator
     * @return array
     */
    protected function getConfig(ServiceLocatorInterface $serviceLocator)
    {
        if ($this->config !== null) {
            return $this->config;
        }

        if (!$serviceLocator->has('Config')) {
            $this->config = [];
            return $this->config;
        }

        $config = $serviceLocator->get('Config');
        if (!isset($config[$this->configKey]) || !is_array($config[$this->configKey])) {
            $this->config = [];
            return $this->config;
        }

        $this->config = $config[$this->configKey];
        return $this->config;
    }
}