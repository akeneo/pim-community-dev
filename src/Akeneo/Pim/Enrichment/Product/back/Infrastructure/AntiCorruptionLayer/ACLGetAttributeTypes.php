<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Infrastructure\AntiCorruptionLayer;

use Akeneo\Pim\Enrichment\Product\Domain\Query\GetAttributeTypes;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ACLGetAttributeTypes implements GetAttributeTypes
{
    public function __construct(private AttributeRepositoryInterface $attributeRepository)
    {
    }

    public function fromAttributeCodes(array $attributeCodes): array
    {
        if ([] === $attributeCodes) {
            return [];
        }

        Assert::allString($attributeCodes);

        return $this->attributeRepository->getAttributeTypeByCodes($attributeCodes);
    }
}
