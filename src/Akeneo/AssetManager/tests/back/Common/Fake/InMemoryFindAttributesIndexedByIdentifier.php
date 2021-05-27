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

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\AssetManager\Domain\Query\Attribute\AttributeDetails;
use Akeneo\AssetManager\Domain\Query\Attribute\FindAttributesIndexedByIdentifierInterface;

/**
 * @author Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class InMemoryFindAttributesIndexedByIdentifier implements FindAttributesIndexedByIdentifierInterface
{
    private InMemoryAttributeRepository $attributeRepository;

    public function __construct(InMemoryAttributeRepository $attributeRepository)
    {
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * @return AttributeDetails[]
     */
    public function find(AssetFamilyIdentifier $assetFamilyIdentifier): array
    {
        $attributes = $this->attributeRepository->findByAssetFamily($assetFamilyIdentifier);

        return array_reduce($attributes, function ($stack, AbstractAttribute $current) {
            $stack[(string) $current->getIdentifier()] = $current;

            return $stack;
        }, []);
    }
}
