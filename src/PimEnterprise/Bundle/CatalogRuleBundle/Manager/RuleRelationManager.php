<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\CatalogRuleBundle\Manager;

use Akeneo\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface;
use Akeneo\Bundle\RuleEngineBundle\Model\RuleInterface;
use Akeneo\Component\Classification\Repository\CategoryRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use PimEnterprise\Component\CatalogRule\Model\FieldImpactActionInterface;
use PimEnterprise\Component\CatalogRule\Model\RuleRelationInterface;
use PimEnterprise\Component\CatalogRule\Repository\RuleRelationRepositoryInterface;
use PimEnterprise\Component\CatalogRule\Model\ProductAddActionInterface;

/**
 * Class RuleRelationManager
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class RuleRelationManager
{
    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var CategoryRepositoryInterface */
    protected $categoryRepository;

    /** @var RuleRelationRepositoryInterface */
    protected $ruleRelationRepo;

    /** @var string */
    protected $attributeClass;

    /** @var string */
    protected $categoryClass;

    /**
     * Constructor
     *
     * @param RuleRelationRepositoryInterface $ruleRelationRepo
     * @param AttributeRepositoryInterface    $attributeRepository
     * @param CategoryRepositoryInterface     $categoryRepository
     * @param string                          $attributeClass
     * @param string                          $categoryClass
     */
    public function __construct(
        RuleRelationRepositoryInterface $ruleRelationRepo,
        AttributeRepositoryInterface $attributeRepository,
        CategoryRepositoryInterface $categoryRepository,
        $attributeClass,
        $categoryClass
    ) {
        $this->ruleRelationRepo    = $ruleRelationRepo;
        $this->attributeRepository = $attributeRepository;
        $this->categoryRepository  = $categoryRepository;
        $this->attributeClass      = $attributeClass;
        $this->categoryClass       = $categoryClass;
    }

    /**
     * Returns all impacted attributes
     *
     * @param RuleInterface $rule
     *
     * @return array
     */
    public function getImpactedElements(RuleInterface $rule)
    {
        $impactedElements = [];
        $fields = [];
        foreach ($rule->getActions() as $action) {
            if ($action instanceof FieldImpactActionInterface) {
                foreach ($action->getImpactedFields() as $impactedField) {
                    if ('categories' === $impactedField && $action instanceof ProductAddActionInterface) {
                        $impactedElements = array_merge(
                            $impactedElements,
                            $this->categoryRepository->getCategoriesByCodes($action->getItems())->toArray()
                        );
                    } else {
                        $fields[] = $impactedField;
                    }
                }
            }
        }

        $fields = array_unique($fields);

        foreach ($fields as $field) {
            $impactedElements[] = $this->attributeRepository->findOneByIdentifier($field);
        }

        return array_filter($impactedElements);
    }

    /**
     * @param mixed $attributeId
     *
     * @return bool
     */
    public function isAttributeImpacted($attributeId)
    {
        return $this->isResourceImpacted($attributeId, $this->attributeClass);
    }

    /**
     * @param mixed  $resourceId
     * @param string $resourceName
     *
     * @return bool
     */
    public function isResourceImpacted($resourceId, $resourceName)
    {
        $resourceName = $this->resolveResourceName($resourceName);

        return $this->ruleRelationRepo->isResourceImpactedByRule($resourceId, $resourceName);
    }

    /**
     * @param int $attributeId
     *
     * @return RuleDefinitionInterface[]
     */
    public function getRulesForAttribute($attributeId)
    {
        return $this->getRulesForResource($attributeId, $this->attributeClass);
    }

    /**
     * Get rules related to a resource
     *
     * @param int    $resourceId
     * @param string $resourceName
     *
     * @return RuleDefinitionInterface[]
     */
    public function getRulesForResource($resourceId, $resourceName)
    {
        $resourceName = $this->resolveResourceName($resourceName);
        $ruleRelations = $this->getRuleRelationsForResource($resourceId, $resourceName);

        $rules = [];
        foreach ($ruleRelations as $ruleRelation) {
            $rules[] = $ruleRelation->getRuleDefinition();
        }

        return $rules;
    }

    /**
     * Get rules relations
     *
     * @param string $resourceId
     * @param string $resourceName
     *
     * @return RuleRelationInterface[]
     */
    protected function getRuleRelationsForResource($resourceId, $resourceName)
    {
        return $this->ruleRelationRepo->findBy(['resourceId' => $resourceId, 'resourceName' => $resourceName]);
    }

    /**
     * @param $resourceName
     *
     * @throws \InvalidArgumentException
     *
     * @return string
     */
    protected function resolveResourceName($resourceName)
    {
        switch ($resourceName) {
            case 'attribute':
            case $this->attributeClass:
                $type = $this->attributeClass;
                break;
            case 'category':
            case $this->categoryClass:
                $type = $this->categoryClass;
                break;
            default:
                $type = null;
        }

        if (null === $type) {
            throw new \InvalidArgumentException(sprintf('The resource name "%s" can not be resolved.', $resourceName));
        }

        return $type;
    }
}
