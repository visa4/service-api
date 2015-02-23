<?php
/**
 * Matryoshka Service API
 *
 * @link        https://github.com/matryoshka-model/service-api
 * @copyright   Copyright (c) 2014, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace Matryoshka\Service\Api\Response\Decoder;

use Matryoshka\Service\Api\Exception;
use Zend\Http\Header\Accept;
use Zend\Http\Response;
use Zend\Json\Json;
use Zend\Stdlib\ArrayUtils;

/**
 * Class Hal
 */
class Hal implements DecoderInterface
{

    /**
     * @var array|null
     */
    protected $lastPayload;

    /**
     * @var bool
     */
    protected $promoteTopCollection = true;

    /**
     * @return boolean
     */
    public function getPromoteTopCollection()
    {
        return $this->promoteTopCollection;
    }

    /**
     * @param bool $promote
     * @return $this
     */
    public function setPromoteTopCollection($promote)
    {
        $this->promoteTopCollection = (bool) $promote;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getAcceptHeader()
    {
        return (new Accept())->addMediaType('application/json');
    }

    /**
     * @return array|null
     */
    public function getLastPayload()
    {
        return $this->lastPayload;
    }

    /**
     * {@inheritdoc}
     */
    public function decode(Response $response)
    {
        $headers = $response->getHeaders();
        if (!$headers->has('Content-Type')) {
            $exception = new Exception\InvalidResponseException('Content-Type missing');
            $exception->setResponse($response);
            throw $exception;
        }
        /* @var $contentType \Zend\Http\Header\ContentType */
        $contentType = $headers->get('Content-Type');
        switch (true) {
            case $contentType->match('*/json'):
                $payload = Json::decode($response->getBody(), Json::TYPE_ARRAY);
                break;
                //TODO: xml
//             case $contentType->match('*/xml'):
//                 $xml = Security::scan($response->getBody());
//                 $payload = Json::decode(Json::encode((array) $xml), Json::TYPE_ARRAY);
//                 break;

            default:
                throw new Exception\InvalidFormatException(sprintf(
                    'The "%s" media type is invalid or not supported',
                    $contentType->getMediaType()
                ));
                break;
        }

        $this->lastPayload = $payload;

        if ($contentType->match('*/hal+*')) {
            return $this->extractResourceFromHal($payload, $this->getPromoteTopCollection());
        }
        //else
        return (array) $payload;
    }

    /**
     * @param array $data
     * @return array
     */
    protected function extractResourceFromHal(array $data, $promoteTopCollection = true)
    {
        if (array_key_exists('_links', $data)) {
            unset($data['_links']);
        }

        if (array_key_exists('_embedded', $data)) {
            $embedded = $data['_embedded'];
            foreach ($embedded as $key => $resourceNode) {
                if (ArrayUtils::isList($resourceNode, true)) { //assume is a collection of resources
                    $temp = [];
                    foreach ($resourceNode as $resource) {
                        $temp[] = $this->extractResourceFromHal($resource, false);
                    }
                    if ($promoteTopCollection) {
                        if (count($embedded) > 1) {
                            throw new Exception\RuntimeException('Cannot promote multiple top collections');
                        }
                        $data = $temp;
                        break;
                    } else {
                        $data[$key] = $temp;
                    }
                } else { //assume is a single resource
                    $data[$key] = $this->extractResourceFromHal($resourceNode, false);
                }
            }
            unset($data['_embedded']);
        }

        return $data;
    }
}
