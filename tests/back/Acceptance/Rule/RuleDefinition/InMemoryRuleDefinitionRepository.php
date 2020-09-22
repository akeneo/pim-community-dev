<?php

namespace AkeneoEnterprise\Test\Acceptance\Rule\RuleDefinition;

use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Repository\RuleDefinitionRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Test\Acceptance\Common\NotImplementedException;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\Assert;

class InMemoryRuleDefinitionRepository implements IdentifiableObjectRepositoryInterface, SaverInterface, RuleDefinitionRepositoryInterface
{
    /** @var ArrayCollection */
    private $ruleDefinitions;

    public function __construct(array $ruleDefinitions = [])
    {
        $this->ruleDefinitions = new ArrayCollection($ruleDefinitions);
    }

    public function getIdentifierProperties()
    {
        return ['code'];
    }

    public function findOneByIdentifier($identifier)
    {
        return $this->ruleDefinitions->get($identifier);
    }

    public function save($ruleDefinition, array $options = [])
    {
        Assert::assertInstanceOf(RuleDefinitionInterface::class, $ruleDefinition);
        /** @var RuleDefinitionInterface $ruleDefinition */
        if (null === $ruleDefinition->getId()) {
            $ruleDefinition->setId(mt_rand());
        }
        $ruleDefinition->setImpactedSubjectCount(null);
        $ruleDefinition->setRelations(new ArrayCollection());

        $this->ruleDefinitions->set($ruleDefinition->getCode(), $ruleDefinition);
    }

    public function find($id)
    {
        throw new NotImplementedException(__METHOD__);
    }

    public function findAll()
    {
        return $this->ruleDefinitions->toArray();
    }

    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        throw new NotImplementedException(__METHOD__);
    }

    public function findOneBy(array $criteria)
    {
        throw new NotImplementedException(__METHOD__);
    }

    public function getClassName()
    {
        throw new NotImplementedException(__METHOD__);
    }

    public function findEnabledOrderedByPriority()
    {
        $ruleDefinitions = $this->ruleDefinitions->toArray();
        $ruleDefinitions = array_filter($ruleDefinitions, function (RuleDefinitionInterface $ruleDefinition): bool {
            return $ruleDefinition->isEnabled();
        });
        usort($ruleDefinitions, function (RuleDefinitionInterface $rule1, RuleDefinitionInterface $rule2): int {
            return $rule2->getPriority() <=> $rule1->getPriority();
        });

        return $ruleDefinitions;
    }

    public function createDatagridQueryBuilder()
    {
        throw new NotImplementedException(__METHOD__);
    }
}
