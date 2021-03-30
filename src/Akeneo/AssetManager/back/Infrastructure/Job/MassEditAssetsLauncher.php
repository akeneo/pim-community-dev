<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Infrastructure\Job;

use Akeneo\AssetManager\Application\Asset\MassEditAssets\MassEditAssetsLauncherInterface;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\Asset\AssetQuery;
use Akeneo\Tool\Component\BatchQueue\Queue\PublishJobToQueue;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/** *
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 */
class MassEditAssetsLauncher implements MassEditAssetsLauncherInterface
{
    private PublishJobToQueue $publishJobToQueue;
    private TokenStorageInterface $tokenStorage;

    public function __construct(PublishJobToQueue $publishJobToQueue, TokenStorageInterface $tokenStorage)
    {
        $this->publishJobToQueue = $publishJobToQueue;
        $this->tokenStorage = $tokenStorage;
    }

    public function launchForAssetFamily(
        AssetFamilyIdentifier $assetFamilyIdentifier,
        AssetQuery $assetQuery,
        array $updaters
    ): void {
        $token = $this->tokenStorage->getToken();
        $username = null !== $token ? $token->getUsername() : null;

        $normalizedUpdaters = array_map(function ($command) {
            return $command->normalize();
        }, $updaters);

        $config = [
            'asset_family_identifier' => (string) $assetFamilyIdentifier,
            'query' => $assetQuery->normalize(),
            'user_to_notify' => $username,
            'updaters' => $normalizedUpdaters
        ];

        $this->publishJobToQueue->publish('asset_manager_mass_edit_assets', $config, false, $username);
    }
}
