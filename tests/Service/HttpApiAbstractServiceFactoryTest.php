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
use Matryoshka\Service\Api\Client\HttpApi;

/**
 * Class HttpApiAbstractServiceFactoryTest
 */
class HttpApiAbstractServiceFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Zend\ServiceManager\ServiceManager
     */
    protected $serviceManager;

    /**
     * @return array
     */
    public function providerValidService()
    {
        return [
            ['HttpApi\Valid'],
        ];
    }

    /**
     * @return array
     */
    public function providerInvalidService()
    {
        return [
            ['HttpApi\Invalid'],
        ];
    }

    public function setUp()
    {
        $config = [
            'matryoshka-service-api' => [
                'HttpApi\Valid' => [
                    'valid_status_code' => [
                        200,
                        201
                    ],
                    'request_format' => 'json',
                    'response_format' => 'json',
                    'profiler' => 'Profiler'
                ],

                'HttpApi\Invalid' => [
                ],
            ],
        ];

        $sm = $this->serviceManager = new ServiceManager(
            new Config([
                'abstract_factories' => [
                    'Matryoshka\Service\Api\Service\HttpApiAbstractServiceFactory',
                ]
            ])
        );

        $sm->setService('Config', $config);

        $profiler = $this->getMock('Matryoshka\Service\Api\Profiler\ProfilerInterface');
        $sm->setService('Profiler', $profiler);
    }

    /**
     * @param $service
     * @dataProvider providerValidService
     */
    public function testCreateService($service)
    {
        $actual = $this->serviceManager->get($service);
        $this->assertInstanceOf('\Matryoshka\Service\Api\Client\HttpApi', $actual);
    }

    /**
     * @param $service
     * @dataProvider providerInvalidService
     */
    public function testNotCreateService($service)
    {
        $this->assertFalse($this->serviceManager->has($service));
    }

    /**
     * @param string $service
     * @dataProvider providerValidService
     * @expectedException \Zend\ServiceManager\Exception\ServiceNotFoundException
     */
    public function testNullConfig($service)
    {
        $sl = new ServiceManager(
            new Config(
                [
                    'abstract_factories' => [
                        'Matryoshka\Service\Api\Service\HttpApiAbstractServiceFactory',
                    ]
                ]
            )
        );
        $sl->get($service);
    }

    /**
     * @param string $service
     * @dataProvider providerValidService
     * @expectedException \Zend\ServiceManager\Exception\ServiceNotFoundException
     */
    public function testEmptyConfig($service)
    {
        $sl = new ServiceManager(
            new Config(
                [
                    'abstract_factories' => [
                        'Matryoshka\Service\Api\Service\HttpApiAbstractServiceFactory',
                    ]
                ]
            )
        );
        $sl->setService('Config', []);
        $sl->get($service);
    }
}
