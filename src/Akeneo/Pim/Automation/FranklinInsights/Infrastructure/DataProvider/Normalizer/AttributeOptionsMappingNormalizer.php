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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\DataProvider\Normalizer;

use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeOption\Model\Write\AttributeOption;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeOption\Model\Write\AttributeOptionsMapping;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\ValueObject\OptionMapping;
use Akeneo\Pim\Structure\Component\Repository\AttributeOptionRepositoryInterface;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class AttributeOptionsMappingNormalizer
{
    /** @var AttributeOptionRepositoryInterface */
    private $attributeOptionRepository;

    /**
     * @param AttributeOptionRepositoryInterface $attributeOptionRepository
     */
    public function __construct(AttributeOptionRepositoryInterface $attributeOptionRepository)
    {
        $this->attributeOptionRepository = $attributeOptionRepository;
    }

    /**
     * @param AttributeOptionsMapping $attributeOptionsMapping
     *
     * @return array
     */
    public function normalize(AttributeOptionsMapping $attributeOptionsMapping): array
    {
        $result = [];

        $optionTranslations = $this->getAllOptionTranslations(
            $attributeOptionsMapping->getAttributeCode(),
            $attributeOptionsMapping->getOptionCodes()
        );

        foreach ($attributeOptionsMapping as $optionMapping) {
            /* @var AttributeOption $optionMapping */
            $result[] = [
                'from' => [
                    'id' => $optionMapping->getFranklinOptionId(),
                    'label' => [
                        'en_US' => $optionMapping->getFranklinOptionLabel(),
                    ],
                ],
                'to' => $this->computeTargetOptionMapping($optionMapping, $optionTranslations),
                'status' => $optionMapping->isMapped() ? OptionMapping::STATUS_ACTIVE : OptionMapping::STATUS_INACTIVE,
            ];
        }

        return $result;
    }

    /**
     * @param AttributeOption $optionMapping
     * @param array $translations
     *
     * @return array|null
     */
    private function computeTargetOptionMapping(AttributeOption $optionMapping, array $translations)
    {
        $to = null;
        if ($optionMapping->isMapped()) {
            $to = [
                'id' => $optionMapping->getPimOptionId(),
                'label' => $translations[$optionMapping->getPimOptionId()] ?? [],
            ];
        }

        return $to;
    }

    /**
     * @param array $optionsCodes
     *
     * @return array
     */
    private function getAllOptionTranslations(AttributeCode $attributeCode, array $optionsCodes)
    {
        $options = $this->attributeOptionRepository->findBy([
            'attribute.code' => (string) $attributeCode,
            'code' => array_values($optionsCodes)
        ]);

        $translationsByOptionCode = [];
        foreach ($options as $option) {
            foreach ($option->getOptionValues() as $translation) {
                $translationsByOptionCode[$option->getCode()][$translation->getLocale()] = $translation->getValue();
            }
        }

        return $translationsByOptionCode;
    }
}
