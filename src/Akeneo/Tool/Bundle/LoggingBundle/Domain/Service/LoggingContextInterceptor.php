<?php


namespace Akeneo\Tool\Bundle\LoggingBundle\Domain\Service;

use CG\Proxy\MethodInterceptorInterface;
use CG\Proxy\MethodInvocation;
use Psr\Log\LoggerInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LoggingContextInterceptor implements MethodInterceptorInterface
{
    use PHPAttributeAware;

    public function intercept(MethodInvocation $invocation)
    {
//        \DateTime::
    }
}
