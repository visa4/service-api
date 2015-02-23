<?php
/**
 * Matryoshka Service API
 *
 * @link        https://github.com/matryoshka-model/service-api
 * @copyright   Copyright (c) 2014, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace MatryoshkaServiceApiTest\Service;

use Zend\ServiceManager\Config;
use Zend\ServiceManager\ServiceManager;
use Zend\Http\Client;

/**
 * Class HttpClientServiceFactoryTest
 */
class HttpClientServiceFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Zend\ServiceManager\ServiceManager
     */
    protected $serviceManager;


    protected $testOptions = [
        'useragent' => 'TestUserAgent',
    ];

    protected $authOptions = [
        'user'      => 'test',
        'password'  => 'test',
        'type'      => Client::AUTH_DIGEST
    ];


    public function setUp()
    {
        $config = [
            'matryoshka-httpclient' => $this->testOptions + [
                    'uri'   => 'http://example.net',
                    'auth'  => $this->authOptions
                ],
        ];

        $sm = $this->serviceManager = new ServiceManager(
            new Config(
                [
                    'factories' => [
                        'HttpClient' => 'Matryoshka\Service\Api\Service\HttpClientServiceFactory',
                    ]
                ]
            )
        );

        $sm->setService('Config', $config);
    }


    public function testCreateService()
    {
        $client = $this->serviceManager->get('HttpClient');
        $this->assertInstanceOf('\Zend\Http\Client', $client);

        $this->assertEquals('http://example.net/', (string)$client->getUri());

        $refl = new \ReflectionClass($client);
        $configProp = $refl->getProperty('config');
        $configProp->setAccessible(true);
        $config = $configProp->getValue($client);

        foreach ($this->testOptions as $key => $value) {
            $this->assertSame($value, $config[$key]);
        }

        $refl = new \ReflectionClass($client);
        $configProp = $refl->getProperty('auth');
        $configProp->setAccessible(true);
        $config = $configProp->getValue($client);

        foreach ($this->authOptions as $key => $value) {
            $this->assertSame($value, $config[$key]);
        }

    }
}
