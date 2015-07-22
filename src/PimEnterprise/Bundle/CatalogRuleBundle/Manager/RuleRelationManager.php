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

use Akeneo\Bundle\RuleEngineBundle\Model\RuleInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use PimEnterprise\Bundle\CatalogRuleBundle\Model\FieldImpactActionInterface;
use PimEnterprise\Bundle\CatalogRuleBundle\Model\RuleRelationInterface;
use PimEnterprise\Bundle\CatalogRuleBundle\Repository\RuleRelationRepositoryInterface;
use Akeneo\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface;

/**
 * Class RuleRelationManager
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class RuleRelationManager
{
    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var RuleRelationRepositoryInterface */
    protected $ruleRelationRepo;

    /** @var string */
    protected $attributeClass;

    /**
     * Constructor
     *
     * @param RuleRelationRepositoryInterface $ruleRelationRepo
     * @param AttributeRepositoryInterface    $attributeRepository
     * @param string                          $attributeClass
     */
    public function __construct(
        RuleRelationRepositoryInterface $ruleRelationRepo,
        AttributeRepositoryInterface $attributeRepository,
        $attributeClass
    ) {
        $this->ruleRelationRepo = $ruleRelationRepo;
        $this->attributeRepository = $attributeRepository;
        $this->attributeClass = $attributeClass;
    }

    /**
     * Returns all impacted attributes
     *
     * @param RuleInterface $rule
     *
     * @return array
     */
    public function getImpactedAttributes(RuleInterface $rule)
    {
        $fields = [];
        foreach ($rule->getActions() as $action) {
            if ($action instanceof FieldImpactActionInterface) {
                $fields = array_merge($fields, $action->getImpactedFields());
            }
        }

        $fields = array_unique($fields);

        $impactedAttributes = [];
        foreach ($fields as $field) {
            $impactedAttributes[] = $this->attributeRepository->findOneByIdentifier($field);
        }

        $impactedAttributes = array_filter($impactedAttributes);

        return $impactedAttributes;
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
     * @param integer $resourceId
     * @param string  $resourceName
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
            default:
                $type = null;
        }

        if (null === $type) {
            throw new \InvalidArgumentException(sprintf('The resource name "%s" can not be resolved.', $resourceName));
        }

        return $type;
    }
}
