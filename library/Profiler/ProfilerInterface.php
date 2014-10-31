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
/**
 * Interface HttpProfilerInterface
 *
 * @package Matryoshka\Service\Api\Profiler
 */
interface ProfilerInterface
{
    /**
     * @return $this
     */
    public function profilerStart();

    /**
     * @param Client $target
     * @return $this
     */
    public function profilerFinish(Client $target);
}
