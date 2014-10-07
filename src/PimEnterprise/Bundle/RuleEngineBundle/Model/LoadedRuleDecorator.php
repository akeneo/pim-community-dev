<?php


namespace PimEnterprise\Bundle\RuleEngineBundle\Model;


class LoadedRuleDecorator implements LoadedRuleDecoratorInterface
{
    /** @var RuleInterface */
    protected $rule;

    /** @var array */
    protected $conditions;

    /**
     * The constructor
     *
     * @param RuleInterface $rule
     */
    public function __construct(RuleInterface $rule)
    {
        $this->rule = $rule;
    }

    /**
     * {@inheritdoc}
     */
    public function getConditions()
    {
        return $this->conditions;
    }

    /**
     * {@inheritdoc}
     */
    public function setConditions(array $conditions)
    {
        $this->conditions = $conditions;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addCondition(array $condition)
    {
        $this->conditions[] = $condition;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->rule->getId();
    }

    /**
     * {@inheritdoc}
     */
    public function getCode()
    {
        return $this->rule->getCode();
    }

    /**
     * {@inheritdoc}
     */
    public function setCode($code)
    {
        $this->rule->setCode($code);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return $this->rule->getType();
    }

    /**
     * {@inheritdoc}
     */
    public function setType($type)
    {
        $this->rule->setType($type);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getContent()
    {
        return $this->rule->getContent();
    }

    /**
     * {@inheritdoc}
     */
    public function setContent($content)
    {
        $this->rule->setContent($content);

        return $this;
    }
}
