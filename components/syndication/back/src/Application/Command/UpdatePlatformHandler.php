<?php

declare(strict_types=1);


namespace Akeneo\Platform\Syndication\Application\Command;

use Akeneo\Platform\Syndication\Domain\Model\Platform;
use Akeneo\Platform\Syndication\Domain\Query\PlatformConfiguration\PlatformRepositoryInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 */
class UpdatePlatformHandler
{
    public function __construct(
        private PlatformRepositoryInterface $platformRepository
    ) {
    }

    public function handle(UpdatePlatformCommand $updatePlatformCommand): void
    {
        $platform = new Platform(
            $updatePlatformCommand->code,
            $updatePlatformCommand->label,
            $updatePlatformCommand->enabled,
            $updatePlatformCommand->families
        );

        $this->platformRepository->save($platform);
    }
}
