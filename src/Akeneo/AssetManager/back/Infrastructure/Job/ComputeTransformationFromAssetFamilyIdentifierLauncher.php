<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Infrastructure\Job;

use Akeneo\AssetManager\Application\Asset\ComputeTransformationsAssets\ComputeTransformationFromAssetFamilyIdentifierLauncherInterface;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\Tool\Component\BatchQueue\Queue\PublishJobToQueue;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ComputeTransformationFromAssetFamilyIdentifierLauncher implements ComputeTransformationFromAssetFamilyIdentifierLauncherInterface
{
    private PublishJobToQueue $publishJobToQueue;

    private TokenStorageInterface $tokenStorage;

    public function __construct(PublishJobToQueue $publishJobToQueue, TokenStorageInterface $tokenStorage)
    {
        $this->publishJobToQueue = $publishJobToQueue;
        $this->tokenStorage = $tokenStorage;
    }

    public function launch(AssetFamilyIdentifier $assetFamilyIdentifier): void
    {
        $config = [
            'asset_family_identifier' => (string) $assetFamilyIdentifier,
        ];

        $token = $this->tokenStorage->getToken();

        $this->publishJobToQueue->publish(
            'asset_manager_compute_transformations',
            $config,
            false,
            null !== $token ? $token->getUsername() : null
        );
    }
}
