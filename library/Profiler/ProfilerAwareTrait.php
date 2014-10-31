<?php
/**
 * Matryoshka Service API
 *
 * @link        https://github.com/matryoshka-model/service-api
 * @copyright   Copyright (c) 2014, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace Matryoshka\Service\Api\Profiler;

/**
 * Class ProfilerAwareTrait
 *
 * @package Matryoshka\Service\Api\Profiler
 */
trait ProfilerAwareTrait
{
    /**
     * @var ProfilerInterface
     */
    protected $profiler;

    /**
     * @return ProfilerInterface
     */
    public function getProfiler()
    {
        return $this->profiler;
    }

    /**
     * @param ProfilerInterface $profiler
     * @return $this
     */
    public function setProfiler(ProfilerInterface $profiler)
    {
        $this->profiler = $profiler;
        return $this;
    }
}
