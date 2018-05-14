<?php

namespace AkeneoEnterprise\Test\Acceptance\Rule\RuleDefinition;

use Akeneo\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface;
use Akeneo\Bundle\RuleEngineBundle\Repository\RuleDefinitionRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Test\Acceptance\Common\NotImplementedException;
use Doctrine\Common\Collections\ArrayCollection;

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
        if (!$ruleDefinition instanceof RuleDefinitionInterface) {
            throw new \InvalidArgumentException('The object argument should be a rule definition');
        }

        $this->ruleDefinitions->set($ruleDefinition->getCode(), $ruleDefinition);
    }

    public function find($id)
    {
        throw new NotImplementedException(__METHOD__);
    }

    public function findAll()
    {
        throw new NotImplementedException(__METHOD__);
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

    public function findAllOrderedByPriority()
    {
        throw new NotImplementedException(__METHOD__);
    }

    public function createDatagridQueryBuilder()
    {
        throw new NotImplementedException(__METHOD__);
    }
}
