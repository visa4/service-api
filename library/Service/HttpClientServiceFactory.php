<?php
/**
 * Matryoshka Service API
 *
 * @link        https://github.com/matryoshka-model/service-api
 * @copyright   Copyright (c) 2014, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace Matryoshka\Service\Api\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\Http\Client;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class HttpClientServiceFactory
 */
class HttpClientServiceFactory implements FactoryInterface
{

    /**
     * @var string
     */
    protected $configKey = 'matryoshka-rest-httpclient';

    /**
     * Create a http client service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return Client
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('Config');

        $client = new Client();
        if (!empty($config[$this->configKey])) {

            $clientOptions = $config[$this->configKey];

            if (isset($clientOptions['uri'])) {
                $client->setUri($clientOptions['uri']);
                unset($clientOptions['uri']);
            }

            $client->setOptions($config[$this->configKey]);
        }

        return $client;
    }
}
