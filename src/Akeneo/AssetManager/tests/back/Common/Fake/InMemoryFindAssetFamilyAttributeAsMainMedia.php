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

namespace Akeneo\AssetManager\Common\Fake;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AttributeAsMainMediaReference;
use Akeneo\AssetManager\Domain\Query\AssetFamily\FindAssetFamilyAttributeAsMainMediaInterface;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyNotFoundException;

class InMemoryFindAssetFamilyAttributeAsMainMedia implements FindAssetFamilyAttributeAsMainMediaInterface
{
    private InMemoryAssetFamilyRepository $assetFamilyRepository;

    public function __construct(InMemoryAssetFamilyRepository $assetFamilyRepository)
    {
        $this->assetFamilyRepository = $assetFamilyRepository;
    }

    public function find(AssetFamilyIdentifier $assetFamilyIdentifier): AttributeAsMainMediaReference
    {
        try {
            $assetFamily = $this->assetFamilyRepository->getByIdentifier($assetFamilyIdentifier);
        } catch (AssetFamilyNotFoundException $e) {
            return AttributeAsMainMediaReference::noReference();
        }

        return $assetFamily->getAttributeAsMainMediaReference();
    }
}
