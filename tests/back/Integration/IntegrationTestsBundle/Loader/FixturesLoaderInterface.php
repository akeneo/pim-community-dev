<?php

declare(strict_types=1);

namespace Akeneo\Test\IntegrationTestsBundle\Loader;

use Akeneo\Test\Integration\Configuration;

/**
 * Aims to load the fixtures before executing a test.
 *
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface FixturesLoaderInterface
{
    /**
     * Loads test catalog accordingly to the given configuration.
     *
     * @param Configuration $configuration
     */
    public function load(Configuration $configuration): void;
}
