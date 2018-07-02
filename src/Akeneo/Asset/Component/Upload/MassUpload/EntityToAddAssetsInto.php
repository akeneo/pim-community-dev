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

namespace Akeneo\Asset\Component\Upload\MassUpload;

/**
 * This DTO is used to add newly uploaded assets to an asset collection field of a product or product model.
 *
 * It contains the ID of the product/product model and the attribute code of the asset collection.
 *
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
final class EntityToAddAssetsInto
{
    /** @var string */
    private $entityIdentifier;

    /** @var string */
    private $attributeCode;

    /**
     * @param string $entityIdentifier
     * @param string $attributeCode
     */
    public function __construct(string $entityIdentifier, string $attributeCode)
    {
        $this->entityIdentifier = $entityIdentifier;
        $this->attributeCode = $attributeCode;
    }

    /**
     * @return string
     */
    public function getEntityIdentifier(): string
    {
        return $this->entityIdentifier;
    }

    /**
     * @return string
     */
    public function getAttributeCode(): string
    {
        return $this->attributeCode;
    }
}
