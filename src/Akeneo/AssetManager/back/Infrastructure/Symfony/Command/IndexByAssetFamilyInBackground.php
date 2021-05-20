<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Infrastructure\Symfony\Command;

use Akeneo\AssetManager\Application\Asset\Subscribers\IndexByAssetFamilyInBackgroundInterface;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\Tool\Component\Console\CommandLauncher;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class IndexByAssetFamilyInBackground implements IndexByAssetFamilyInBackgroundInterface
{
    private CommandLauncher $commandLauncher;

    public function __construct(CommandLauncher $commandLauncher)
    {
        $this->commandLauncher = $commandLauncher;
    }

    public function execute(AssetFamilyIdentifier $assetFamilyIdentifier): void
    {
        $cmd = sprintf(
            '%s %s',
            IndexAssetsCommand::INDEX_ASSETS_COMMAND_NAME,
            (string) $assetFamilyIdentifier
        );

        $this->commandLauncher->executeBackground($cmd, '/dev/null');
    }
}
