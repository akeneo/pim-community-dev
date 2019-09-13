<?php
declare(strict_types=1);

namespace Akeneo\AssetManager\Infrastructure\Validation\AssetFamily\ProductLinkRules;

use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * This query is responsible for fetching the asset family identifier a product asset collection attribute.
 */
interface FindAssetCollectionTypeACLInterface
{
    public function fetch(string $productAttributeCode): string;
}
