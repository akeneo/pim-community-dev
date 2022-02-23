<?php


namespace Akeneo\Tool\Bundle\LoggingBundle\Domain\Factory;

use Akeneo\Tool\Bundle\LoggingBundle\Domain\Model\AttributesBag;
use Akeneo\Tool\Bundle\LoggingBundle\Domain\Model\InterceptorAttributesBag;
use CG\Proxy\MethodInterceptorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InterceptorAttributesBagFactory
{
    private $interceptors = [];

    public function __construct()
    {
    }


    public function create(AttributesBag $interceptorAttributesBag): InterceptorAttributesBag
    {
        return new InterceptorAttributesBag();
    }
}