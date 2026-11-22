<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\InstallerBundle\Migration;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Doctrine migrations implementing this interface receive the service container before being run.
 *
 * It replaces Symfony's ContainerAwareInterface, removed in Symfony 7, which the Doctrine migrations bundle
 * relied on to inject the container.
 *
 * @copyright 2026 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ContainerAwareMigrationInterface
{
    public function setContainer(?ContainerInterface $container = null): void;
}
