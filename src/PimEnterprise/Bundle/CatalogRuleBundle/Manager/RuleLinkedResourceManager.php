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

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeRepository;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Component\Resource\Model\RemoverInterface;
use Pim\Component\Resource\Model\SaverInterface;
use PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductCopyValueActionInterface;
use PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductSetValueActionInterface;
use PimEnterprise\Bundle\CatalogRuleBundle\Model\RuleLinkedResourceInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface;

/**
 * Class RuleLinkedResourceManager
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class RuleLinkedResourceManager implements SaverInterface, RemoverInterface
{
    /** @var EntityManager */
    protected $entityManager;

    /** @var AttributeRepository */
    protected $attributeRepository;

    /** @var EntityRepository */
    protected $ruleLinkedResRepo;

    /**
     * Constructor
     *
     * @param EntityManager       $entityManager
     * @param AttributeRepository $attributeRepository
     * @param EntityRepository    $ruleLinkedResRepo
     */
    public function __construct(
        EntityManager $entityManager,
        AttributeRepository $attributeRepository,
        EntityRepository $ruleLinkedResRepo
    ) {
        $this->entityManager       = $entityManager;
        $this->attributeRepository = $attributeRepository;
        $this->ruleLinkedResRepo   = $ruleLinkedResRepo;
    }

    /**
     * {@inheritdoc}
     */
    public function save($object, array $options = [])
    {
        if (!$object instanceof RuleLinkedResourceInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a use PimEnterprise\Bundle\CatalogRuleBundle\Model\RuleLinkedResourceInterface,
                    "%s" provided',
                    ClassUtils::getClass($object)
                )
            );
        }

        $options = array_merge(['flush' => true], $options);
        $this->entityManager->persist($object);

        if (true === $options['flush']) {
            $this->entityManager->flush();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function remove($object, array $options = [])
    {
        if (!$object instanceof RuleLinkedResourceInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a use PimEnterprise\Bundle\CatalogRuleBundle\Model\RuleLinkedResourceInterface,
                    "%s" provided',
                    ClassUtils::getClass($object)
                )
            );
        }

        $this->entityManager->remove($object);
    }

    /**
     * Returns all impacted attributes
     *
     * @param array $actions
     *
     * @return array
     */
    public function getImpactedAttributes(array $actions)
    {
        $fields = [];
        foreach ($actions as $action) {
            if ($action instanceof ProductCopyValueActionInterface) {
                $fields[] = $action->getToField();
            } elseif ($action instanceof ProductSetValueActionInterface) {
                $fields[] = $action->getField();
            }
        }

        $impactedAttributes = [];
        foreach ($fields as $field) {
            $impactedAttributes[] = $this->attributeRepository->findByReference($field);
        }

        $impactedAttributes = array_unique($impactedAttributes);
        $impactedAttributes = array_filter($impactedAttributes);

        return $impactedAttributes;
    }

    /**
     * @param int $attributeId
     *
     * @return bool
     */
    public function isImpactedAttribute($attributeId)
    {
        //todo: better check
        return $this->ruleLinkedResRepo->findBy(['resourceId' => $attributeId]) ? true : false;
    }

    /**
     * @param int $attributeId
     *
     * @return RuleDefinitionInterface[]
     */
    public function getRulesForAttribute($attributeId)
    {
        $ruleLinkedResources = $this->ruleLinkedResRepo->findBy(['resourceId' => $attributeId]);

        $rules = [];
        foreach ($ruleLinkedResources as $ruleLinkedResource) {
            $rules[] = $ruleLinkedResource->getRule();
        }

        return $rules;
    }
}
