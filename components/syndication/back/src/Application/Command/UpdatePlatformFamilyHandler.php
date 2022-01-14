<?php

declare(strict_types=1);


namespace Akeneo\Platform\Syndication\Application\Command;

use Akeneo\Platform\Syndication\Domain\Model\PlatformFamily;
use Akeneo\Platform\Syndication\Domain\Query\PlatformConfiguration\PlatformRepositoryInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 */
class UpdatePlatformFamilyHandler
{
    public function __construct(
        private PlatformRepositoryInterface $platformRepository
    ) {
    }

    public function handle(UpdatePlatformFamilyCommand $updatePlatformCommand): void
    {
        $platformFamily = new PlatformFamily(
            $updatePlatformCommand->code,
            $updatePlatformCommand->platformCode,
            $updatePlatformCommand->label,
            $updatePlatformCommand->requirements
        );

        $this->platformRepository->saveFamily($platformFamily);
    }
}
