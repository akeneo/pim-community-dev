<?php

namespace Oro\Bundle\FilterBundle\Extension\Orm;

use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;

abstract class AbstractFilter implements FilterInterface
{
    /** @var string */
    protected $name;

    /** @var array */
    protected $params;

    /** @var FormFactoryInterface */
    protected $formFactory;

    /** @var Form */
    protected $form;

    public function __construct(FormFactoryInterface $factory)
    {
        $this->formFactory = $factory;
    }

    /**
     * {@inheritDoc}
     */
    public function init($name, array $params)
    {
        $this->name   = $name;
        $this->params = $params;
    }

    /**
     * {@inheritDoc}
     */
    public function getForm()
    {
        if (!$this->form) {
            $this->form = $this->formFactory->create(
                $this->getFormType(),
                array(),
                array_merge($this->getOr('options', array()), array('csrf_protection' => false))
            );
        }

        return $this->form;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns form type associated to this filter
     *
     * @return mixed
     */
    abstract protected function getFormType();

    /**
     * @param QueryBuilder $qb
     * @param mixed        $parameter
     */
    protected function applyWhere(QueryBuilder $qb, $parameter)
    {
        /** @var QueryBuilder $queryBuilder */
        if ($this->getOr('filter_condition', self::CONDITION_AND) == self::CONDITION_OR) {
            $qb->orWhere($parameter);
        } else {
            $qb->andWhere($parameter);
        }
    }

    /**
     * Apply expression to having clause
     *
     * @param QueryBuilder $qb
     * @param mixed        $parameter
     */
    protected function applyHaving(QueryBuilder $qb, $parameter)
    {
        /** @var $queryBuilder QueryBuilder */
        if ($this->getOr('filter_condition', self::CONDITION_AND) == self::CONDITION_OR) {
            $qb->orHaving($parameter);
        } else {
            $qb->andHaving($parameter);
        }
    }

    /**
     * Apply filter expression to having or where clause depending on configuration
     *
     * @param QueryBuilder $qb
     * @param mixed        $expression
     */
    protected function applyFilterToClause(QueryBuilder $qb, $expression)
    {
        if ($this->getOr('filter_by_having', false)) {
            $this->applyHaving($qb, $expression);
        } else {
            $this->applyWhere($qb, $expression);
        }
    }

    /**
     * Get param or throws exception
     *
     * @param string $paramName
     *
     * @throws \LogicException
     * @return mixed
     */
    protected function get($paramName)
    {
        if (!isset($this->params[$paramName])) {
            throw new \LogicException(sprintf('Trying to access not existing parameter: "%s"', $paramName));
        }

        return $this->params[$paramName];
    }

    /**
     * Get param if exists or default value
     *
     * @param string $paramName
     * @param null   $default
     *
     * @return mixed
     */
    protected function getOr($paramName, $default = null)
    {
        return isset($this->params[$paramName]) ? $this->params[$paramName] : $default;
    }
}
