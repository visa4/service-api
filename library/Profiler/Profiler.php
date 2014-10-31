<?php
/**
 * Matryoshka Service API
 *
 * @link        https://github.com/matryoshka-model/service-api
 * @copyright   Copyright (c) 2014, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace Matryoshka\Service\Api\Profiler;

use Zend\Http\Client;
use Zend\Http\Request;
use Zend\Http\Response;

/**
 * Class HttpProfiler
 *
 * @package Matryoshka\Service\Api\Profiler
 */
class Profiler implements ProfilerInterface
{
    /**
     * @var array
     */
    protected $profiles = [];

    /**
     * @var null
     */
    protected $currentIndex = 0;

    /**
     * @return $this
     */
    public function profilerStart()
    {
        $profileInformation = [
            'request' => null,
            'response' => null,
            'start' => microtime(true),
            'end' => null,
            'elapse' => null
        ];

        $this->profiles[$this->currentIndex] = $profileInformation;
        return $this;
    }

    /**
     * @param Client $target
     * @return $this
     */
    public function profilerFinish(Client $target)
    {
        $current = &$this->profiles[$this->currentIndex];

        $current['end'] = microtime(true);
        $current['elapse'] = $current['end'] - $current['start'];

        $current['request']  = (string) $target->getLastRawRequest();
        $current['response'] = (string) $target->getLastRawResponse();

        $this->currentIndex++;
        return $this;
    }

    /**
     * @return array
     */
    public function getProfiles()
    {
        return $this->profiles;
    }
}
