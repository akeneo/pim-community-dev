<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Model\QuantifiedAssociation;

use Webmozart\Assert\Assert;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class QuantifiedAssociations
{
    private function __construct(array $quantifiedAssociations)
    {
    }

    public static function createWithAssociationsAndMapping(
        array $rawQuantifiedAssociations,
        IdMapping $mappedProductIds,
        IdMapping $mappedProductModelIds
    ): self {
        // TODO:
        return new self();
    }

    public function getQuantifiedAssociationsProductIds(): array
    {
        return [];
    }

    public function getQuantifiedAssociationsProductModelIds(): array
    {
        return [];
    }

    public function getQuantifiedAssociationsProductIdentifiers(): array
    {
        return [];
    }

    public function getQuantifiedAssociationsProductModelCodes(): array
    {
        return [];
    }

}
