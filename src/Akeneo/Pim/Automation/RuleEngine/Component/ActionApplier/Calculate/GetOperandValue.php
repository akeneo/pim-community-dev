<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\RuleEngine\Component\ActionApplier\Calculate;

use Akeneo\Pim\Automation\RuleEngine\Component\Model\Operand;
use Akeneo\Pim\Automation\RuleEngine\Component\Model\ProductTarget;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Bundle\MeasureBundle\Convert\MeasureConverter;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Webmozart\Assert\Assert;

class GetOperandValue
{
    /** @var IdentifiableObjectRepositoryInterface */
    private $attributeRepository;

    /** @var MeasureConverter */
    private $measureConverter;

    public function __construct(
        IdentifiableObjectRepositoryInterface $attributeRepository,
        MeasureConverter $measureConverter
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->measureConverter = $measureConverter;
    }

    public function fromEntity(EntityWithValuesInterface $entity, Operand $operand): ?float
    {
        $value = $entity->getValue(
            $operand->getAttributeCode(),
            $operand->getLocaleCode(),
            $operand->getChannelCode()
        );
        if (null === $value) {
            return null;
        }

        $attribute = $this->attributeRepository->findOneByIdentifier($operand->getAttributeCode());
        Assert::isInstanceOf($attribute, AttributeInterface::class);

        switch ($attribute->getType()) {
            case AttributeTypes::NUMBER:
                return (float)$value->getData();
            case AttributeTypes::PRICE_COLLECTION:
                Assert::string($operand->getCurrencyCode());
                $price = $value->getPrice($operand->getCurrencyCode());

                return null !== $price ? (float) $price->getData() : null;
            case AttributeTypes::METRIC:
                $metric = $value->getData();
                $this->measureConverter->setFamily($attribute->getMetricFamily());
                $converted = $this->measureConverter->convert(
                    $metric->getUnit(),
                    $attribute->getDefaultMetricUnit(),
                    $metric->getData()
                );

                return (float) $converted;
            default:
                return null;
        }
    }
}
