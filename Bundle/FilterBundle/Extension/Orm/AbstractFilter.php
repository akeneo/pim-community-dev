<?php

namespace Oro\Bundle\FilterBundle\Extension\Orm;

use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;

use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;

use Oro\Bundle\FilterBundle\Extension\Configuration;

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

    /**
     * Map configuration keys to metadata keys
     *
     * @var array
     */
    protected $paramMap = [self::FRONTEND_TYPE_KEY => self::TYPE_KEY];

    /** @var array */
    protected $excludeParams = [];

    /** @var array */
    protected $excludeParamsDefault = [self::DATA_NAME_KEY, self::FORM_OPTIONS_KEY];

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
                [],
                array_merge($this->getOr(self::FORM_OPTIONS_KEY, []), ['csrf_protection' => false])
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
     * {@inheritdoc}
     */
    public function getMetadata()
    {
        $formView = $this->getForm()->createView();
        $typeView = $formView->children['type'];

        $defaultMetadata = [
            'name'                     => $this->getName(),
            // use filter name if label not set
            'label'                    => ucfirst($this->name),
            'choices'                  => $typeView->vars['choices'],
            Configuration::ENABLED_KEY => true,
        ];

        $metadata = array_diff_key(
            $this->get(),
            array_flip(array_merge($this->excludeParams, $this->excludeParamsDefault))
        );
        $metadata = $this->mapParams($metadata);
        $metadata = array_merge($defaultMetadata, $metadata);

        return $metadata;
    }

    /**
     * Returns form type associated to this filter
     *
     * @return mixed
     */
    abstract protected function getFormType();

    /**
     * Applies expression to where clause
     *
     * @param QueryBuilder $qb
     * @param mixed        $parameter
     */
    protected function applyWhere(QueryBuilder $qb, $parameter)
    {
        if ($this->fixComparison($qb, $parameter)) {
            return;
        }

        /** @var QueryBuilder $queryBuilder */
        if ($this->getOr('filter_condition', self::CONDITION_AND) == self::CONDITION_OR) {
            $qb->orWhere($parameter);
        } else {
            $qb->andWhere($parameter);
        }
    }

    /**
     * Applies expression to having clause
     *
     * @param QueryBuilder $qb
     * @param mixed        $parameter
     */
    protected function applyHaving(QueryBuilder $qb, $parameter)
    {
        if ($this->fixComparison($qb, $parameter)) {
            return;
        }

        /** @var $queryBuilder QueryBuilder */
        if ($this->getOr('filter_condition', self::CONDITION_AND) == self::CONDITION_OR) {
            $qb->orHaving($parameter);
        } else {
            $qb->andHaving($parameter);
        }
    }

    /**
     * Note: this is workaround for http://www.doctrine-project.org/jira/browse/DDC-1858
     * It could be removed when doctrine version >= 2.4
     *
     * @param QueryBuilder $qb
     * @param mixed        $parameter
     *
     * @return bool
     */
    private function fixComparison(QueryBuilder $qb, $parameter)
    {
        if ($parameter instanceof \Doctrine\ORM\Query\Expr\Comparison
            && ($parameter->getOperator() === 'LIKE' || $parameter->getOperator() === 'NOT LIKE')) {
            $extraSelect   = null;
            $expectedAlias = (string)$parameter->getLeftExpr();

            foreach ($qb->getDQLPart('select') as $selectPart) {
                foreach ($selectPart->getParts() as $part) {
                    if (preg_match("#(.*)\\s+as\\s+$expectedAlias#i", $part, $matches)) {
                        $extraSelect = $matches[1];
                    }
                }
            }

            if ($extraSelect !== null) {
                $isParam   = preg_match('#^:{1}#', $parameter->getRightExpr());
                $parameter = $this->createComparisonExpression(
                    $extraSelect,
                    $parameter->getOperator(),
                    $parameter->getRightExpr(),
                    !$isParam
                );

                $this->applyWhere($qb, $parameter);

                return true;
            }
        }

        return false;
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
     * Create filter expression that will be applied
     *
     * @param mixed  $leftExpression
     * @param string $operator
     * @param mixed  $rightExpression
     * @param bool   $withParam
     *
     * @return \Doctrine\ORM\Query\Expr\Comparison
     */
    protected function createComparisonExpression($leftExpression, $operator, $rightExpression, $withParam = true)
    {
        $rightExpression = $withParam ? ':' . $rightExpression : $rightExpression;

        return new Expr\Comparison($leftExpression, $operator, $rightExpression);
    }

    /**
     * Generates unique param name
     *
     * @return string
     */
    protected function generateQueryParameterName()
    {
        return preg_replace('#[^a-z0-9]#i', '', $this->getName()) . mt_rand();
    }

    /**
     * Get param or throws exception
     *
     * @param string $paramName
     *
     * @throws \LogicException
     * @return mixed
     */
    protected function get($paramName = null)
    {
        $value = $this->params;

        if ($paramName !== null) {
            if (!isset($this->params[$paramName])) {
                throw new \LogicException(sprintf('Trying to access not existing parameter: "%s"', $paramName));
            }

            $value = $this->params[$paramName];
        }

        return $value;
    }

    /**
     * Get param if exists or default value
     *
     * @param string $paramName
     * @param null   $default
     *
     * @return mixed
     */
    protected function getOr($paramName = null, $default = null)
    {
        if ($paramName !== null) {
            return isset($this->params[$paramName]) ? $this->params[$paramName] : $default;
        }

        return $this->params;
    }

    /**
     * Process mapping params
     *
     * @param array $params
     *
     * @return array
     */
    protected function mapParams($params)
    {
        $keys = [];
        foreach (array_keys($params) as $key) {
            if (isset($this->paramMap[$key])) {
                $keys[] = $this->paramMap[$key];
            } else {
                $keys[] = $key;
            }
        }

        return array_combine($keys, array_values($params));
    }
}
