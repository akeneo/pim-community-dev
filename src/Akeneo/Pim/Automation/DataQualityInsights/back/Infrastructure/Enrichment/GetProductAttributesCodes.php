<?php
declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Enrichment;

use Akeneo\Pim\Automation\DataQualityInsights\Application\GetProductAttributesCodesInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetAttributesByTypeFromProductQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetLocalizableAttributesByTypeFromProductQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Structure\Component\AttributeTypes;

final class GetProductAttributesCodes implements GetProductAttributesCodesInterface
{
    /** @var GetAttributesByTypeFromProductQueryInterface */
    private $getAttributesByTypeFromProductQuery;

    /** @var GetLocalizableAttributesByTypeFromProductQueryInterface */
    private $getLocalizableAttributesByTypeFromProductQuery;

    public function __construct(
        GetAttributesByTypeFromProductQueryInterface $getAttributesByTypeFromProductQuery,
        GetLocalizableAttributesByTypeFromProductQueryInterface $getLocalizableAttributesByTypeFromProductQuery
    ) {
        $this->getAttributesByTypeFromProductQuery = $getAttributesByTypeFromProductQuery;
        $this->getLocalizableAttributesByTypeFromProductQuery = $getLocalizableAttributesByTypeFromProductQuery;
    }

    public function getTextarea(ProductId $productId): array
    {
        return $this->getAttributesByTypeFromProductQuery->execute($productId, AttributeTypes::TEXTAREA);
    }

    public function getText(ProductId $productId): array
    {
        return $this->getAttributesByTypeFromProductQuery->execute($productId, AttributeTypes::TEXT);
    }

    public function getLocalizableText(ProductId $productId): array
    {
        return $this->getLocalizableAttributesByTypeFromProductQuery->execute($productId, AttributeTypes::TEXT);
    }
}
