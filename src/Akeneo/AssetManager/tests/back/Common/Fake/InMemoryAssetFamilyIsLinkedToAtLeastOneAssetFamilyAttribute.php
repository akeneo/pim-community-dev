<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Common\Fake;

use Akeneo\AssetManager\Domain\Model\Attribute\AssetAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\AssetCollectionAttribute;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\AssetFamily\AssetFamilyIsLinkedToAtLeastOneAssetFamilyAttributeInterface;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class InMemoryAssetFamilyIsLinkedToAtLeastOneAssetFamilyAttribute implements AssetFamilyIsLinkedToAtLeastOneAssetFamilyAttributeInterface
{
    /** @var InMemoryAttributeRepository */
    private $attributeRepository;

    public function __construct(InMemoryAttributeRepository $attributeRepository)
    {
        $this->attributeRepository = $attributeRepository;
    }

    public function isLinked(AssetFamilyIdentifier $identifier): bool
    {
        foreach ($this->attributeRepository->getAttributes() as $attribute) {
            if ($attribute instanceof AssetAttribute || $attribute instanceof AssetCollectionAttribute) {
                if ($attribute->getAssetType()->equals($identifier)) {
                    return true;
                }
            }
        }

        return false;
    }
}
