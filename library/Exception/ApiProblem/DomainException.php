<?php
/**
 * Matryoshka Service API
 *
 * @link        https://github.com/matryoshka-model/service-api
 * @copyright   Copyright (c) 2014, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace Matryoshka\Service\Api\Exception\ApiProblem;

use Matryoshka\Service\Api\Exception\ExceptionInterface;
use ZF\ApiProblem\Exception\DomainException as ZFApiProblemDomainException;

/**
 * Class DomainException
 */
class DomainException extends ZFApiProblemDomainException implements ExceptionInterface
{
}
