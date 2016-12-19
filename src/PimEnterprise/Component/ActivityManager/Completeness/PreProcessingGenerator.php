<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ActivityManager\Completeness;

use Pim\Component\Catalog\Repository\AttributeGroupRepositoryInterface;
use PimEnterprise\Component\ActivityManager\Repository\FamilyRequirementRepositoryInterface;
use PimEnterprise\Component\ActivityManager\Repository\PreProcessingRepositoryInterface;
use PimEnterprise\Component\ActivityManager\Repository\StructuredAttributeRepositoryInterface;

/**
 * Generates the pre processing completeness data.
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class PreProcessingGenerator implements PreProcessingGeneratorInterface
{
    /** @var PreProcessingRepositoryInterface */
    protected $preProcessingRepository;

    /** @var FamilyRequirementRepositoryInterface */
    protected $familyRequirementRepository;

    /** @var StructuredAttributeRepositoryInterface */
    protected $structuredAttributeRepository;

    /** @var AttributeGroupRepositoryInterface */
    protected $attributeGroupRepository;

    /**
     * @param PreProcessingRepositoryInterface       $preProcessingRepository
     * @param StructuredAttributeRepositoryInterface $familyRequirementRepository
     * @param StructuredAttributeRepositoryInterface $structuredAttributeRepository
     * @param AttributeGroupRepositoryInterface      $attributeGroupRepository
     */
    public function __construct(
        PreProcessingRepositoryInterface $preProcessingRepository,
        StructuredAttributeRepositoryInterface $familyRequirementRepository,
        StructuredAttributeRepositoryInterface $structuredAttributeRepository,
        AttributeGroupRepositoryInterface $attributeGroupRepository
    ) {
        $this->preProcessingRepository = $preProcessingRepository;
        $this->familyRequirementRepository = $familyRequirementRepository;
        $this->structuredAttributeRepository = $structuredAttributeRepository;
        $this->attributeGroupRepository = $attributeGroupRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function generate($productId, $channelId, $localeId)
    {
        $structuredAttributes = $this->structuredAttributeRepository
            ->getStructuredAttributes($productId, $channelId, $localeId);

        $structuredMandatoryAttributes = $this->familyRequirementRepository
            ->getStructuredAttributes($structuredAttributes['familyCode'], $channelId, $localeId);

        $comparedAttributes = $this->compare($structuredAttributes, $structuredMandatoryAttributes);

        $this->fillTable($productId, $channelId, $localeId, $comparedAttributes);
    }

    /**
     * Compares values
     *
     * @param array $data
     * @param array $requirements
     *
     * @return array
     */
    protected function compare(array $data, array $requirements)
    {
        $missing = [];
        $full = [];
        $todo = [];
        foreach ($requirements as $attributeGroup => $attributes) {
            if (!array_key_exists($attributeGroup, $data)) {
                $todo[] = $attributeGroup;
                continue;
            }

            if (!empty(array_diff($attributes, $data[$attributeGroup]))) {
                $missing[] = $attributeGroup;
            } else {
                $full[] = $attributeGroup;
            }
        }

        return ['at_least' => array_unique($missing), 'done' => array_unique($full), 'todo' => array_unique($todo)];
    }

    /**
     * Fills the table regarding the compared values.
     *
     * @param int    $productId
     * @param string $channelId
     * @param string $localeId
     * @param array  $comparedAttributes
     */
    protected function fillTable($productId, $channelId, $localeId, array $comparedAttributes)
    {
        foreach ($comparedAttributes['at_least'] as $attributeGroupCode) {
            $attributeGroup = $this->attributeGroupRepository->find($attributeGroupCode);

            $this->preProcessingRepository
                ->addPreProcessingData($productId, $attributeGroup->getId(), 1, 0, $channelId, $localeId);
        }

        foreach ($comparedAttributes['todo'] as $attributeGroupCode) {
            $attributeGroup = $this->attributeGroupRepository->find($attributeGroupCode);

            $this->preProcessingRepository
                ->addPreProcessingData($productId, $attributeGroup->getId(), 0, 0, $channelId, $localeId);
        }

        foreach ($comparedAttributes['done'] as $attributeGroupCode) {
            $attributeGroup = $this->attributeGroupRepository->find($attributeGroupCode);

            $this->preProcessingRepository
                ->addPreProcessingData($productId, $attributeGroup->getId(), 0, 1, $channelId, $localeId);
        }
    }
}
