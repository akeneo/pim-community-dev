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

namespace Akeneo\AssetManager\Application\Asset\MassEditAssets\CommandFactory;

use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditAssetCommandFactory;
use Akeneo\AssetManager\Application\Asset\MassEditAssets\MassEditAssetsCommand;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\Asset\AssetQuery;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 */
class MassEditAssetsCommandFactory
{
    private EditAssetCommandFactory $editAssetCommandFactory;

    public function __construct(EditAssetCommandFactory $editAssetCommandFactory) {
        $this->editAssetCommandFactory = $editAssetCommandFactory;
    }

    public function create(
        AssetFamilyIdentifier $assetFamilyIdentifier,
        AssetQuery $query,
        array $normalizedUpdaters
    ): MassEditAssetsCommand {
        if (!$this->isValidUpdaters($normalizedUpdaters)) {
            throw new BadRequestHttpException('Impossible to create a command of mass asset edition.');
        }

        $updaters = array_map(function ($normalizedUpdater) use ($assetFamilyIdentifier) {
            $fakeEditAssetCommand = $this->editAssetCommandFactory->create([
                'asset_family_identifier' => (string) $assetFamilyIdentifier,
                'code' => 'FAKE_CODE_FOR_MASS_EDIT_VALIDATION_' . microtime(),
                'values' => [
                    [
                        'attribute' => $normalizedUpdater['attribute'],
                        'channel' => $normalizedUpdater['channel'],
                        'locale' => $normalizedUpdater['locale'],
                        'data' => $normalizedUpdater['data'],
                    ]
                ]
            ]);

            return [
                'action' => $normalizedUpdater['action'],
                'id' => $normalizedUpdater['id'],
                'command' => $fakeEditAssetCommand->editAssetValueCommands[0]
            ];
        }, $normalizedUpdaters);

        return new MassEditAssetsCommand((string) $assetFamilyIdentifier, $query->normalize(), $updaters);
    }

    private function isValidUpdaters(array $normalizedUpdaters): bool
    {
        foreach ($normalizedUpdaters as $normalizedUpdater) {
            if (!array_key_exists('attribute', $normalizedUpdater)
                || !array_key_exists('channel', $normalizedUpdater)
                || !array_key_exists('locale', $normalizedUpdater)
                || !array_key_exists('data', $normalizedUpdater)
                || !array_key_exists('action', $normalizedUpdater)
                || !array_key_exists('id', $normalizedUpdater)) {
                return false;
            }
        }

        return true;
    }
}
