<?php
/**
 * Matryoshka Service API
 *
 * @link        https://github.com/matryoshka-model/service-api
 * @copyright   Copyright (c) 2014, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace MatryoshkaServiceApiTest\Exception;

use Matryoshka\Service\Api\Exception\InvalidResponseException;
use Zend\Http\Response;

/**
 * Class InvalidResponseExceptionTest
 */
class InvalidResponseExceptionTest extends \PHPUnit_Framework_TestCase
{

    public function testGetSetResponse()
    {
        $response = new Response();
        $ex = new InvalidResponseException();

        $ex->setResponse($response);
        $this->assertSame($response, $ex->getResponse());
    }
}
