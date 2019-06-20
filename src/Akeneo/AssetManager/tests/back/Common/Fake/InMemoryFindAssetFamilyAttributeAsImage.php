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

use Akeneo\AssetManager\Domain\Model\AssetFamily\AttributeAsImageReference;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\AssetFamily\FindAssetFamilyAttributeAsImageInterface;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyNotFoundException;

class InMemoryFindAssetFamilyAttributeAsImage implements FindAssetFamilyAttributeAsImageInterface
{
    /** @var InMemoryAssetFamilyRepository */
    private $assetFamilyRepository;

    public function __construct(InMemoryAssetFamilyRepository $assetFamilyRepository)
    {
        $this->assetFamilyRepository = $assetFamilyRepository;
    }

    public function find(AssetFamilyIdentifier $assetFamilyIdentifier): AttributeAsImageReference
    {
        try {
            $assetFamily = $this->assetFamilyRepository->getByIdentifier($assetFamilyIdentifier);
        } catch (AssetFamilyNotFoundException $e) {
            return AttributeAsImageReference::noReference();
        }

        return $assetFamily->getAttributeAsImageReference();
    }
}
