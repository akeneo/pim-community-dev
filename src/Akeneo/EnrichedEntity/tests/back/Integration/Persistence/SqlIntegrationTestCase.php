<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\tests\back\Integration\Persistence;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * This class is used for running integration tests testing the database.
 *
 * Every service definition of repositories or query functions use the SQL implementation executing real database
 * calls. Indeed, those tests need a testing database up and running.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SqlIntegrationTestCase extends KernelTestCase
{
    /** @var KernelInterface */
    protected $kernel;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        static::bootKernel(['debug' => false]);
        $this->kernel = new \AppKernelTest('SqlIntegrationTest', false);
        $this->kernel->boot();
    }
}
