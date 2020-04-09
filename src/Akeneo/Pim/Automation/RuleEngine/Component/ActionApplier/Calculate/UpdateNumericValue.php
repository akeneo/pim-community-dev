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

use Akeneo\Pim\Automation\RuleEngine\Component\Model\ProductTarget;
use Akeneo\Pim\Enrichment\Component\Product\Builder\EntityWithValuesBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Webmozart\Assert\Assert;

class UpdateNumericValue
{
    /** @var IdentifiableObjectRepositoryInterface */
    private $attributeRepository;

    /** @var EntityWithValuesBuilderInterface */
    private $entityWithValuesBuilder;

    /** @var NormalizerInterface */
    private $normalizer;

    public function __construct(
        IdentifiableObjectRepositoryInterface $attributeRepository,
        EntityWithValuesBuilderInterface $entityWithValuesBuilder,
        NormalizerInterface $normalizer
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->entityWithValuesBuilder = $entityWithValuesBuilder;
        $this->normalizer = $normalizer;
    }

    public function forEntity(EntityWithValuesInterface $entity, ProductTarget $destination, float $data): void
    {
        /** @var AttributeInterface $attribute */
        $attribute = $this->attributeRepository->findOneByIdentifier($destination->getField());
        Assert::isInstanceOf($attribute, AttributeInterface::class);

        $formattedData = null;

        switch ($attribute->getType()) {
            case AttributeTypes::NUMBER:
                $formattedData = $data;
                break;
            case AttributeTypes::METRIC:
                // TODO RUL-62: convert to destination unit
                $formattedData = [
                    'amount' => $data,
                    'unit' => $attribute->getDefaultMetricUnit(),
                ];
                break;
            case AttributeTypes::PRICE_COLLECTION:
                $formattedData = $this->getPriceCollectionData($entity, $destination, $data);
                break;
            default:
                throw new \InvalidArgumentException('Unsupported destination type');
        }

        $this->entityWithValuesBuilder->addOrReplaceValue(
            $entity,
            $attribute,
            $destination->getLocale(),
            $destination->getScope(),
            $formattedData
        );
    }

    private function getPriceCollectionData(
        EntityWithValuesInterface $entity,
        ProductTarget $destination,
        float $amount
    ): array {
        Assert::string($destination->getCurrency());
        $standardizedPrices = [
            [
                'amount' => $amount,
                'currency' => $destination->getCurrency(),
            ]
        ];

        $previousValue = $entity->getValue(
            $destination->getField(),
            $destination->getLocale(),
            $destination->getScope()
        );
        if (null === $previousValue) {
            return $standardizedPrices;
        }

        foreach ($previousValue->getData() as $previousPrice) {
            if ($previousPrice->getCurrency() !== $destination->getCurrency()) {
                $standardizedPrices[] = $this->normalizer->normalize($previousPrice, 'standard');
            }
        }

        return $standardizedPrices;
    }
}
