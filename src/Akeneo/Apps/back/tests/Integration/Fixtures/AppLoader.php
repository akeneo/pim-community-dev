<?php

declare(strict_types=1);

namespace Akeneo\Apps\back\tests\Integration\Fixtures;

use Akeneo\Apps\Application\Command\CreateAppCommand;
use Akeneo\Apps\Application\Command\CreateAppHandler;
use Akeneo\Apps\Domain\Model\Read\AppWithCredentials;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AppLoader
{
    /** @var CreateAppHandler*/
    private $createAppHandler;

    public function __construct(CreateAppHandler $createAppHandler)
    {
        $this->createAppHandler = $createAppHandler;
    }

    public function createApp(string $code, string $label, string $flowType): AppWithCredentials
    {
        $command = new CreateAppCommand($code, $label, $flowType);
        return $this->createAppHandler->handle($command);
    }
}
