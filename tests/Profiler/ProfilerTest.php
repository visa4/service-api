<?php
/**
 * Matryoshka Service API
 *
 * @link        https://github.com/matryoshka-model/service-api
 * @copyright   Copyright (c) 2014, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace MatryoshkaServiceApiTest\Profiler;

use Matryoshka\Service\Api\Profiler\Profiler;
use Zend\Http\Client;
use Zend\Http\Response;

/**
 * Class ProfilerTest
 */
class ProfilerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Matryoshka\Service\Api\Profiler\Profiler
     */
    protected $profiler;

    public function setUp()
    {
        $this->profiler = new Profiler();
    }

    public function testProfilerStart()
    {
        $this->assertSame($this->profiler, $this->profiler->profilerStart());
    }

    public function testProfilerStop()
    {
        $client = new Client();
        $this->profiler->profilerStart();
        $this->assertSame($this->profiler, $this->profiler->profilerFinish($client));
    }

    public function testGetProfiles()
    {
        $this->assertEmpty($this->profiler->getProfiles());
    }
}
