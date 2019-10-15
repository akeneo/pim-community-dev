<?php

declare(strict_types=1);

namespace Akeneo\Apps\Application\Command;

use Akeneo\Apps\Domain\Model\Write\App;
use Akeneo\Apps\Domain\Persistence\Repository\AppRepository;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class CreateAppHandler
{
    private $repository;

    public function __construct(AppRepository $repository)
    {
        $this->repository = $repository;
    }

    public function handle(CreateAppCommand $command): void
    {
        // TODO: Validate code unicity

        $app = App::create(
            $command->appCode(),
            $command->appLabel(),
            $command->flowType()
        );
        $this->repository->create($app);
    }
}
