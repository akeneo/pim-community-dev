<?php

declare(strict_types=1);

namespace Akeneo\Apps\back\tests\EndToEnd;

use Akeneo\Test\Integration\TestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
abstract class WebTestCase extends TestCase
{
    /** @var KernelBrowser */
    protected $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = self::$container->get('test.client');
    }

    protected function createClient(): KernelBrowser
    {
        return self::$container->get('test.client');
    }
}
