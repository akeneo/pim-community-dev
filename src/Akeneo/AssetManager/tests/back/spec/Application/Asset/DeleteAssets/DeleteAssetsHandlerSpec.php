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

namespace spec\Akeneo\AssetManager\Application\Asset\DeleteAssets;

use Akeneo\AssetManager\Application\Asset\DeleteAssets\DeleteAssetsCommand;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Repository\AssetRepositoryInterface;
use PhpSpec\ObjectBehavior;

class DeleteAssetsHandlerSpec extends ObjectBehavior
{
    public function let(AssetRepositoryInterface $repository)
    {
        $this->beConstructedWith($repository);
    }

    function it_deletes_a_asset_by_its_code_and_entity_identifier(AssetRepositoryInterface $repository)
    {
        $command = new DeleteAssetsCommand(
            'packshot',
            ['packshot_1', 'packshot_2']
        );

        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('packshot');
        $assetCodes = [AssetCode::fromString('packshot_1'), AssetCode::fromString('packshot_2')];

        $repository->deleteByAssetFamilyAndCodes($assetFamilyIdentifier, $assetCodes)->shouldBeCalled();

        $this->__invoke($command);
    }
}
