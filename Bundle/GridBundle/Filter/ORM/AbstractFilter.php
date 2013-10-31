<?php

namespace Oro\Bundle\GridBundle\Filter\ORM;

use Symfony\Component\Translation\TranslatorInterface;

use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\ORM\Query\Expr;

use Oro\Bundle\FilterBundle\Form\Type\Filter\FilterType;
use Oro\Bundle\GridBundle\Filter\FilterInterface;
use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;

abstract class AbstractFilter extends AbstractDescriptiveFilter implements FilterInterface
{
    /**
     * @var Expr
     */
    private $expressionFactory;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function initialize($name, array $options = array())
    {
        $this->name = $name;
        $this->setOptions($options);
    }

    /**
     * {@inheritdoc}
     */
    protected function association(ProxyQueryInterface $queryBuilder, $value)
    {
        $alias = $queryBuilder->entityJoin($this->getParentAssociationMappings());

        $fieldMapping = $this->getFieldMapping();
        if (!empty($fieldMapping['entityAlias'])) {
            $alias = $fieldMapping['entityAlias'];
        }
        return array($alias, $this->getFieldName());
    }

    /**
     * Checks if filter expression should be applied to having clause, if not where clause should be applied
     *
     * @return bool
     */
    protected function isApplyFilterToHavingClause()
    {
        $fieldMapping = $this->getFieldMapping();
        if (!empty($fieldMapping['filterByHaving'])) {
            return true;
        } elseif (!empty($fieldMapping['filterByWhere'])) {
            return false;
        } else {
            return !empty($fieldMapping['fieldExpression']);
        }
    }

    /**
     * Get field expression based on field name and alias. If field mapping has specific expression it will be
     * used instead as is.
     *
     * @param string $fieldName
     * @param string $alias
     * @return string
     */
    protected function createFieldExpression($fieldName, $alias)
    {
        $fieldMapping = $this->getFieldMapping();
        if (!empty($fieldMapping['fieldExpression'])) {
            return $fieldMapping['fieldExpression'];
        } else {
            return sprintf('%s.%s', $alias, $fieldName);
        }
    }

    /**
     * Create filter expression that will be applied
     *
     * @param mixed $leftExpression
     * @param string $operator
     * @param mixed $rightExpression
     * @return Expr\Comparison
     */
    protected function createComparisonExpression($leftExpression, $operator, $rightExpression)
    {
        return new Expr\Comparison($leftExpression, $operator, $rightExpression);
    }

    /**
     * Create comparison expression for field
     *
     * @param string $field
     * @param string $alias
     * @param string $operator
     * @param string $parameterName
     * @return Expr\Comparison
     */
    protected function createCompareFieldExpression($field, $alias, $operator, $parameterName)
    {
        return $this->createComparisonExpression(
            $this->createFieldExpression($field, $alias),
            $operator,
            ':' . $parameterName
        );
    }

    /**
     * Get expression factory
     *
     * @return Expr
     */
    protected function getExpressionFactory()
    {
        if (!$this->expressionFactory) {
            $this->expressionFactory = new Expr();
        }
        return $this->expressionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function apply($queryBuilder, $value)
    {
        $this->value = $value;
        if (is_array($value) && array_key_exists("value", $value)) {
            list($alias, $field) = $this->association($queryBuilder, $value);

            $this->filter($queryBuilder, $alias, $field, $value);
        }
    }

    /**
     * @param ProxyQueryInterface $queryBuilder
     * @param mixed $parameter
     */
    protected function applyWhere(ProxyQueryInterface $queryBuilder, $parameter)
    {
        /** @var QueryBuilder $queryBuilder */
        if ($this->getCondition() == self::CONDITION_OR) {
            $queryBuilder->orWhere($parameter);
        } else {
            $queryBuilder->andWhere($parameter);
        }

        // filter is active since it's added to the queryBuilder
        $this->active = true;
    }

    /**
     * Apply expression to having clause
     *
     * @param ProxyQueryInterface $queryBuilder
     * @param mixed $parameter
     */
    protected function applyHaving(ProxyQueryInterface $queryBuilder, $parameter)
    {
        /** @var $queryBuilder QueryBuilder */
        if ($this->getCondition() == self::CONDITION_OR) {
            $queryBuilder->orHaving($parameter);
        } else {
            $queryBuilder->andHaving($parameter);
        }

        // filter is active since it's added to the queryBuilder
        $this->active = true;
    }

    /**
     * Apply filter expression to having or where clause depending on configuration
     *
     * @param ProxyQueryInterface $queryBuilder
     * @param mixed $expression
     */
    protected function applyFilterToClause(ProxyQueryInterface $queryBuilder, $expression)
    {
        if ($this->isApplyFilterToHavingClause()) {
            $this->applyHaving($queryBuilder, $expression);
        } else {
            $this->applyWhere($queryBuilder, $expression);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions()
    {
        return array();
    }

    /**
     * {@inheritdoc}
     */
    public function getRenderSettings()
    {
        $formType = $this->getOption('form_type', FilterType::NAME);
        $formOptions = array();
        if ($this->getOption('field_type')) {
            $formOptions['field_type'] = $this->getOption('field_type');
        }
        if ($this->getFieldOptions()) {
            $formOptions['field_options'] = $this->getFieldOptions();
        }
        if ($this->getLabel()) {
            $formOptions['label'] = $this->getLabel();
        }
        $formOptions['show_filter'] = $this->getOption('show_filter', false);
        return array($formType, $formOptions);
    }

    /**
     * @param ProxyQueryInterface $proxyQuery
     *
     * @return string
     */
    protected function getNewParameterName(ProxyQueryInterface $proxyQuery)
    {
        // dots are not accepted in a DQL identifier so replace them
        // by underscores.
        return str_replace('.', '_', $this->getName()) . '_' . $proxyQuery->getUniqueParameterId();
    }
}
