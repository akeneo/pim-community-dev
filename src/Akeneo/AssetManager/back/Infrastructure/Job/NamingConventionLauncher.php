<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Infrastructure\Job;

use Akeneo\AssetManager\Application\Asset\ExecuteNamingConvention\NamingConventionLauncherInterface;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\Tool\Component\BatchQueue\Queue\PublishJobToQueue;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @copyright 2020 Akeneo SAS (https://www.akeneo.com)
 */
class NamingConventionLauncher implements NamingConventionLauncherInterface
{
    private PublishJobToQueue $publishJobToQueue;

    private TokenStorageInterface $tokenStorage;

    public function __construct(
        PublishJobToQueue $publishJobToQueue,
        TokenStorageInterface $tokenStorage
    ) {
        $this->publishJobToQueue = $publishJobToQueue;
        $this->tokenStorage = $tokenStorage;
    }

    public function launchForAllAssetFamilyAssets(AssetFamilyIdentifier $assetFamilyIdentifier): void
    {
        $this->publishJobToQueue->publish(
            'asset_manager_execute_naming_convention',
            [
                'asset_family_identifier' => (string)$assetFamilyIdentifier,
            ],
            false,
            $this->getUsername()
        );
    }

    private function getUsername(): ?string
    {
        $token = $this->tokenStorage->getToken();

        return null !== $token ? $token->getUsername() : null;
    }
}
