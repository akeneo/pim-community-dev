<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\EndToEnd;

use Akeneo\Connectivity\Connection\Application\Settings\Command\CreateConnectionCommand;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\Read\ConnectionWithCredentials;
use Akeneo\Test\Integration\TestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
abstract class CommandTestCase extends TestCase
{
    protected $application;

    protected function setUp(): void
    {
        parent::setUp();

        $this->application = new Application($this->testKernel);
    }

    protected function createConnection(string $code, string $label, string $flowType): ConnectionWithCredentials
    {
        $createConnectionCommand = new CreateConnectionCommand($code, $label, $flowType);

        return $this->get('akeneo_connectivity.connection.application.handler.create_connection')
            ->handle($createConnectionCommand);
    }
}
