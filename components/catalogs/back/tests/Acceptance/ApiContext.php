<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Acceptance;

use Behat\Behat\Context\Context;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ApiContext implements Context
{
    private ContainerInterface $container;
    private ?Response $response;

    public function __construct(
        private KernelInterface $kernel,
        private AuthenticationContext $authentication,
    ) {
        $this->container = $kernel->getContainer()->get('test.service_container');
    }
}
