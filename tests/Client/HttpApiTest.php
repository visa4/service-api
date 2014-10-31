<?php
/**
 * Matryoshka Service API
 *
 * @link        https://github.com/matryoshka-model/service-api
 * @copyright   Copyright (c) 2014, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace MatryoshkaServiceApiTest\Client;

use Zend\Http\Request;
use Zend\Http\Response;
use Matryoshka\Service\Api\Client\HttpApi;

/**
 * Class HttpApiTest
 */
class HttpApiTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var HttpApi
     */
    protected $httpApi;

    /**
     * @return array
     */
    public function providerServiceResponse()
    {
        return [
            [['get', 'path', ['test' => 'test']], '{"test": "test"}', 'application/json', 'json'],
            [['get', null, ['test' => 'test']], '{"test": "test"}', 'application/json', 'json'],
            [['head', 'path', ['test' => 'test']], '{"test": "test"}', 'application/json', 'json'],
            [['options', 'path', ['test' => 'test']], '{"test": "test"}', 'application/json', 'json'],
            [['patch', 'path', ['test' => 'test']], '{"test": "test"}', 'application/json', 'json'],
            [['post', 'path', ['test' => 'test'], ['test' => 'test']], '{"test": "test"}', 'application/json', 'json'],
            [['put', 'path', ['test' => 'test'], ['test' => 'test']], '{"test": "test"}', 'application/json', 'json'],
            [['delete', 'path', ['test' => 'test']], '{"test": "test"}', 'application/json', 'json'],
        ];
    }

    /**
     * @return array
     */
    public function providerServiceRequestResponseException()
    {
        //Params: [$params, $responseContent, $responseContentType, $responseStatusCode, $format, $exceptionType]

        $apiProblemResponse = '{
    "type": "http://example.com/probs/out-of-credit",
    "title": "You do not have enough credit.",
    "detail": "Your current balance is 30, but that costs 50.",
    "instance": "http://example.net/account/12345/msgs/abc",
    "balance": 30,
    "accounts": ["http://example.net/account/12345",
                 "http://example.net/account/67890"],
    "exception_stack": [
    {
      "code": 0,
      "message": "first",
      "trace": [
        {
          "file": "/../...php",
          "line": 82,
          "function": "get",
          "class": "class",
          "type": "->"
        }
      ]
    }
    ]
    }';

        return [
            //Bad responses
            [['get', null], '{"test": "test"}', 'application/json', 500, 'json'],
            [['post', 'path', ['test' => 'test']], '{"test": "test"}', 'application/json', 500, 'json'],
            [['delete', 'id'], '', 'application/json', 500, 'json'],
            [
                ['get', null],
                $apiProblemResponse,
                'application/problem+json',
                500,
                'json',
                '\Matryoshka\Service\Api\Exception\ApiProblem\DomainException'
            ],
            [
                ['get', 'id'],
                '',
                'application/problem+json',
                502,
                'json',
                '\Matryoshka\Service\Api\Exception\ApiProblem\DomainException'
            ],
            [
                ['get', 'id'],
                '',
                'application/invalid-response-format',
                502,
                'json',
                '\Matryoshka\Service\Api\Exception\InvalidFormatException'
            ],
            [['get', 'id'], null, '', 502, 'json'], //content-type missing

            //Bad requests
            [
                ['post', 'path', ['test' => 'test']],
                '',
                'application/json',
                502,
                'invalid-request-format',
                '\Matryoshka\Service\Api\Exception\InvalidFormatException'
            ],
        ];
    }

    public function setUp()
    {
        $this->httpApi = new HttpApi();
    }

    public function testGetSetValidStatusCodes()
    {
        $this->assertSame($this->httpApi, $this->httpApi->setValidStatusCodes([200, 201]));
        $this->assertCount(2, $this->httpApi->getValidStatusCodes());
    }

    public function testGetSetRequestFormat()
    {
        $this->assertSame($this->httpApi, $this->httpApi->setRequestFormat('json'));
        $this->assertSame('json', $this->httpApi->getRequestFormat());
    }

    public function testGetSetBaseRequest()
    {
        $request = new Request();
        $this->assertSame($this->httpApi, $this->httpApi->setBaseRequest($request));
        $this->assertSame($request, $this->httpApi->getBaseRequest());
    }

    public function testGetLastRequest()
    {
        $this->assertNull($this->httpApi->getLastRequest());
    }

    public function testGetLastResponse()
    {
        $this->assertNull($this->httpApi->getLastResponse());
    }


    public function testGetLastResponseData()
    {
        $this->assertNull($this->httpApi->getLastResponseData());
    }

    public function testCloneBaseRequest()
    {
        $request = $this->httpApi->getBaseRequest();
        $cloneRequest = $this->httpApi->cloneBaseRequest();
        $this->assertInstanceOf('Zend\Http\Request', $cloneRequest);
        $this->assertNotSame($request, $cloneRequest);
    }

    /**
     * @param array $params
     * @param $contentResponse
     * @param $responseContentType
     * @param $typeResponse
     * @dataProvider providerServiceResponse
     */
    public function testHttpMethod(array $params, $contentResponse, $responseContentType, $typeResponse)
    {
        $httpClient = $this->getMockBuilder('Zend\Http\Client')
            ->disableOriginalConstructor()
            ->setMethods(['dispatch', 'getResponse'])
            ->getMock();

        $expectedResponse = new Response();
        $expectedResponse->setContent($contentResponse);
        $expectedResponse->getHeaders()->addHeaderLine('Content-Type', $responseContentType);

        $httpClient->expects($this->any())
            ->method('dispatch')
            ->will($this->returnValue($expectedResponse));

        $httpClient->expects($this->any())
            ->method('getResponse')
            ->will($this->returnValue($expectedResponse));

        $api = new HttpApi($httpClient);
        $profiler = $this->getMock('Matryoshka\Service\Api\Profiler\ProfilerInterface');

        $api->setRequestFormat($typeResponse);
        $api->setProfiler($profiler);

        $request = call_user_func_array([$api, 'prepareRequest'], $params);
        $this->assertInstanceOf('\Zend\Http\Request', $request);
        $this->assertEquals(
            $api->getBaseRequest()->getUri()->getPath() . (string)$params[1],
            $request->getUri()->getPath()
        );

        $response = $api->dispatchRequest($request);
        $this->assertSame($api->getResponseDecoder()->decode($expectedResponse), $response);


        $this->assertSame($request, $api->getLastRequest());
        $this->assertSame($expectedResponse, $api->getLastResponse());
        $this->assertSame($api->getResponseDecoder()->getLastPayload(), $api->getLastResponseData());
    }

    /**
     * @param array $params
     * @param $responseContent
     * @param $responseContentType
     * @param $responseStatusCode
     * @param $format
     * @param string $exceptionType
     * @dataProvider providerServiceRequestResponseException
     */
    public function testHttpMethodRequestResponseException(
        array $params,
        $responseContent,
        $responseContentType,
        $responseStatusCode,
        $format,
        $exceptionType = '\Matryoshka\Service\Api\Exception\InvalidResponseException'
    ) {
        $httpClient = $this->getMockBuilder('Zend\Http\Client')
            ->disableOriginalConstructor()
            ->setMethods(['dispatch', 'getResponse'])
            ->getMock();

        $response = new Response();
        $response->setContent($responseContent);
        if ($responseContentType) {
            $response->getHeaders()->addHeaderLine('Content-Type: ' . $responseContentType);
        }
        $response->setStatusCode($responseStatusCode);


        $httpClient->expects($this->any())
            ->method('dispatch')
            ->will($this->returnValue($response));

        $httpClient->expects($this->any())
            ->method('getResponse')
            ->will($this->returnValue($response));

        $api = new HttpApi($httpClient);
        $api->setRequestFormat($format);

        $this->setExpectedException($exceptionType);
        $api->dispatchRequest(call_user_func_array([$api, 'prepareRequest'], $params));
    }


    public function testPrepareRequestShouldThrowExceptionOnInvalidFormat()
    {
        $this->httpApi->setRequestFormat('invalid format');

        $this->setExpectedException('\Matryoshka\Service\Api\Exception\InvalidFormatException');
        $this->httpApi->prepareRequest('post', null, ['foo' => 'baz']);
    }
}
