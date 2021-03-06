<?php
/**
 * Matryoshka Service API
 *
 * @link        https://github.com/matryoshka-model/service-api
 * @copyright   Copyright (c) 2014, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace Matryoshka\Service\Api\Response\Decoder;

use Zend\Http\Header\Accept;
use Zend\Http\Response;

/**
 * Interface DecoderInterface
 */
interface DecoderInterface
{
    /**
     * @param Response $response
     * @return array
     */
    public function decode(Response $response);

    /**
     * @return array|null
     */
    public function getLastPayload();

    /**
     * @return Accept
     */
    public function getAcceptHeader();
}
