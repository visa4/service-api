<?php
/**
 * Matryoshka Service API
 *
 * @link        https://github.com/matryoshka-model/service-api
 * @copyright   Copyright (c) 2014, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace Matryoshka\Service\Api\Client;

use Matryoshka\Service\Api\Exception;
use Matryoshka\Service\Api\Profiler\ProfilerAwareInterface;
use Matryoshka\Service\Api\Profiler\ProfilerAwareTrait;
use Matryoshka\Service\Api\Response\Decoder\DecoderInterface;
use Matryoshka\Service\Api\Response\Decoder\Hal;
use Zend\Http\Request;
use Zend\Http\Client;
use Zend\Json\Json;
use Zend\Http\Header\ContentType;
use Zend\Http\Response;

/**
 * Class HttpApi
 */
class HttpApi implements ProfilerAwareInterface
{
    use ProfilerAwareTrait;

    const FORMAT_JSON = 'json';
    const FORMAT_XML = 'xml';

    /**
     * @var Client
     */
    public $httpClient;

    /**
     * @var Request
     */
    protected $baseRequest;

    /**
     * @var DecoderInterface
     */
    protected $responseDecoder;

    /**
     * @var array
     */
    protected $validStatusCodes = [];

    /**
     * @var string
     */
    protected $requestFormat = self::FORMAT_JSON;

    /**
     * @var Request
     */
    protected $lastRequest = null;

    /**
     * @var Response
     */
    protected $lastResponse = null;

    /**
     * @param Client $httpClient
     * @param Request $baseRequest
     */
    public function __construct(Client $httpClient = null, Request $baseRequest = null)
    {
        $this->httpClient = $httpClient ? $httpClient : new Client();
        $this->baseRequest = $baseRequest ? $baseRequest : $this->httpClient->getRequest();
    }

    /**
     * @param $method
     * @param null $relativePath
     * @param array $data
     * @param array $query
     * @return Request
     */
    public function prepareRequest($method, $relativePath = null, array $data = [], array $query = [])
    {
        $request = $this->cloneBaseRequest();
        $request->setMethod($method);
        if ($relativePath) {
            $request->getUri()->setPath($request->getUri()->getPath() . $relativePath);
        }
        $queryParams = $request->getQuery();
        foreach ($query as $name => $value) {
            $queryParams->set($name, $value);
        }

        if (!empty($data)) {
            $request->setContent($this->encodeBodyRequest($data));
        }

        $request->getHeaders()->addHeaderLine('Content-Type', 'application/' . $this->getRequestFormat())
                ->addHeader($this->getResponseDecoder()->getAcceptHeader());
        return $request;
    }

    /**
     * Prepare a POST request for uploading files
     *
     * The request method will be set to POST and multipart/form-data will be used
     * as content type, ignoring the current request format.
     *
     * $files is treated as:
     * [
     *    name => localFilePath,
     *    ...
     * ]
     *
     * $data will be used for other data data without the filename segment.
     *
     * Each localFilePath will be read and sent. Will try to guess the content type using mime_content_type().
     * By default, the basename of localFilePath will be sent as filename segment, if a 'name' is also present
     * in $data then the value of $data[name] will be used as filename segment.
     *
     *
     *
     * @param array $files
     * @param string $relativePath
     * @param array $data
     * @param array $query
     * @return \Zend\Http\Request
     */
    public function prepareFileUploadRequest(array $files, $relativePath = null, array $data = [], array $query = [])
    {
        $request = $this->prepareRequest('POST', $relativePath, [], $query);
        $request->getHeaders()->removeHeader($request->getHeaders()->get('Content-Type'));

        $this->httpClient->setRequest($request);

        foreach ($files as $formName => $filePath) {
            $this->httpClient->setFileUpload($filePath, $formName);

            if (isset($data[$formName])) {
                $file = $request->getFiles()->get($filePath, null);

                if ($file) { // If present, override the filename
                    $file['filename'] = $data[$formName];
                    $request->getFiles()->set($filePath, $file);
                }

                unset($data[$formName]);
            }
        }

        $request->getPost()->fromArray($data);

        return $request;
    }


    /**
     * @param Request $request
     * @return array
     */
    public function dispatchRequest(Request $request)
    {
        if ($this->profiler) {
            $this->getProfiler()->profilerStart();
        }

        // Send request
        /** @var $response Response */
        $response = $this->httpClient->dispatch($request);

        if ($this->profiler) {
            $this->getProfiler()->profilerFinish($this->httpClient);
        }

        $this->lastRequest = $request;
        $this->lastResponse = $response;

        $validStatusCodes = $this->getValidStatusCodes();
        $responseStatusCode = $response->getStatusCode();
        $decodedResponse = (array)$this->getResponseDecoder()->decode($response);

        if ((empty($validStatusCodes) && $response->isSuccess())
            || in_array($responseStatusCode, $validStatusCodes)
        ) {
            return $decodedResponse;
        }

        throw $this->getInvalidResponseException($decodedResponse, $response);
    }

    /**
     * @param array $data
     * @return string
     */
    protected function encodeBodyRequest(array $data)
    {
        $requestFormat = $this->getRequestFormat();

        switch ($requestFormat) {
            case self::FORMAT_JSON:
                $bodyRequest = Json::encode($data);
                break;
            case self::FORMAT_XML:

                // TODO: not yet implemented
                // break;
            default:
                throw new Exception\InvalidFormatException(
                    sprintf(
                        'The "%s" format is invalid or not supported',
                        $requestFormat
                    )
                );
                break;
        }

        return $bodyRequest;
    }

    /**
     * @param array $bodyDecodeResponse
     * @param Response $response
     * @return Exception\ApiProblem\DomainException|Exception\InvalidResponseException
     */
    protected function getInvalidResponseException(array $bodyDecodeResponse, Response $response)
    {
        $contentType = $response->getHeaders()->get('Content-Type');

        if ($contentType instanceof ContentType && $contentType->match('application/problem+*')) {

            $apiProblemDefaults = [
                'type' => $response->getReasonPhrase(),
                'title' => '',
                'status' => $response->getStatusCode(),
                'detail' => '',
                'instance' => '',
            ];

            $bodyDecodeResponse += $apiProblemDefaults;

            //Setup remote exception
            $remoteExceptionStack = isset($bodyDecodeResponse['exception_stack']) && is_array(
                $bodyDecodeResponse['exception_stack']
            ) ?
                $bodyDecodeResponse['exception_stack'] : [];

            array_unshift(
                $remoteExceptionStack,
                [
                    'message' => $bodyDecodeResponse['detail'],
                    'code' => $bodyDecodeResponse['status'],
                    'trace' => isset($bodyDecodeResponse['trace']) ? $bodyDecodeResponse['trace'] : null,
                ]
            );

            //Setup exception
            $exception = new Exception\ApiProblem\DomainException(
                $bodyDecodeResponse['detail'],
                $bodyDecodeResponse['status'],
                Exception\RemoteException::factory(
                    $remoteExceptionStack
                ) //Set remote ex chain as previous of current ex
            );
            $exception->setType($bodyDecodeResponse['type']);
            $exception->setTitle($bodyDecodeResponse['title']);
            foreach ($apiProblemDefaults as $key => $value) {
                unset($bodyDecodeResponse[$key]);
            }
            $exception->setAdditionalDetails($bodyDecodeResponse);
        } else {
            $exception = new Exception\InvalidResponseException(
                $response->getReasonPhrase(),
                $response->getStatusCode()
            );
            $exception->setResponse($response);
        }

        return $exception;
    }

    /**
     * @return DecoderInterface
     */
    public function getResponseDecoder()
    {
        if (null === $this->responseDecoder) {
            $this->setResponseDecoder(new Hal);
        }

        return $this->responseDecoder;
    }

    /**
     * @param DecoderInterface $decoder
     * @return $this
     */
    public function setResponseDecoder(DecoderInterface $decoder)
    {
        $this->responseDecoder = $decoder;
        return $this;
    }

    /**
     * @return array
     */
    public function getValidStatusCodes()
    {
        return $this->validStatusCodes;
    }

    /**
     * @param array $validStatusCodes
     * @return $this
     */
    public function setValidStatusCodes(array $validStatusCodes)
    {
        $this->validStatusCodes = $validStatusCodes;
        return $this;
    }

    /**
     * @return string
     */
    public function getRequestFormat()
    {
        return $this->requestFormat;
    }

    /**
     * @param $requestFormat
     * @return $this
     */
    public function setRequestFormat($requestFormat)
    {
        $this->requestFormat = $requestFormat;
        return $this;
    }

    /**
     * @return Request
     */
    public function getBaseRequest()
    {
        return $this->baseRequest;
    }

    /**
     * @param Request $request
     * @return $this
     */
    public function setBaseRequest(Request $request)
    {
        $this->baseRequest = $request;
        return $this;
    }

    /**
     * @return Request
     */
    public function cloneBaseRequest()
    {
        return unserialize(serialize($this->baseRequest));
    }

    /**
     * @return Request
     */
    public function getLastRequest()
    {
        return $this->lastRequest;
    }

    /**
     * @return Response
     */
    public function getLastResponse()
    {
        return $this->lastResponse;
    }

    /**
     * @return array|null
     */
    public function getLastResponseData()
    {
        return $this->getResponseDecoder()->getLastPayload();
    }
}
