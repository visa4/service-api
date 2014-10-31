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
 * Interface ProfilerAwareInterface
 *
 * @package Matryoshka\Service\Api\Profiler
 */
interface ProfilerAwareInterface
{
    /**
     * @return ProfilerInterface
     */
    public function getProfiler();

    /**
     * @param ProfilerInterface $profiler
     */
    public function setProfiler(ProfilerInterface $profiler);
}
