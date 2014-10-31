<?php
/**
 * Matryoshka Service API
 *
 * @link        https://github.com/matryoshka-model/service-api
 * @copyright   Copyright (c) 2014, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace MatryoshkaServiceApiTest\Profiler;

/**
 * Class ProfilerAwareTraitTest
 */
class ProfilerAwareTraitTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var $mockTrait
     */
    protected $mockTrait;

    public function setUp()
    {
        $this->mockTrait = $this->getMockForTrait('Matryoshka\Service\Api\Profiler\ProfilerAwareTrait');
    }

    public function testProfilerAwareTraitGetSet()
    {
        $profiler = $this->getMock('Matryoshka\Service\Api\Profiler\ProfilerInterface');
        $this->mockTrait->setProfiler($profiler);
        $this->assertSame($profiler, $this->mockTrait->getProfiler());
    }
}
