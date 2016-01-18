<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter;

use Doctrine\MongoDB\Query\Expr;
use Pim\Bundle\CatalogBundle\Doctrine\Common\Filter\ObjectIdResolverInterface;
use Pim\Bundle\CatalogBundle\Query\Filter\FieldFilterHelper;
use Pim\Bundle\CatalogBundle\Query\Filter\FieldFilterInterface;
use Pim\Bundle\CatalogBundle\Query\Filter\Operators;

/**
 * Family filter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyFilter extends AbstractFilter implements FieldFilterInterface
{
    /** @var array */
    protected $supportedFields;

    /** @var ObjectIdResolverInterface */
    protected $objectIdResolver;

    /**
     * Instanciate the filter
     *
     * @param ObjectIdResolverInterface $objectIdResolver
     * @param array                     $supportedFields
     * @param array                     $supportedOperators
     */
    public function __construct(
        ObjectIdResolverInterface $objectIdResolver,
        array $supportedFields = [],
        array $supportedOperators = []
    ) {
        $this->objectIdResolver   = $objectIdResolver;
        $this->supportedFields    = $supportedFields;
        $this->supportedOperators = $supportedOperators;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsField($field)
    {
        return in_array($field, $this->supportedFields);
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldFilter($field, $operator, $value, $locale = null, $scope = null, $options = [])
    {
        if (Operators::IS_EMPTY !== $operator) {
            $this->checkValue($field, $value);

            if (FieldFilterHelper::getProperty($field) === FieldFilterHelper::CODE_PROPERTY) {
                $value = $this->objectIdResolver->getIdsFromCodes('family', $value);
            }
        }

        $fieldCode = FieldFilterHelper::getCode($field);
        switch ($operator) {
            case Operators::IN_LIST:
                $expr = new Expr();
                $this->qb->addAnd(
                    $expr->field($fieldCode)->in($value)
                );
                break;
            case Operators::NOT_IN_LIST:
                $this->qb->field($fieldCode)->notIn($value);
                break;
            case Operators::IS_EMPTY:
                $exists = new Expr();
                $equals = new Expr();
                $expr = new Expr();
                $exists->field($fieldCode)->exists(false);
                $equals->field($fieldCode)->equals(null);
                $expr->addOr($exists)->addOr($equals);
                $this->qb->addAnd($expr);
                break;
        }

        return $this;
    }

    /**
     * Check if value is valid
     *
     * @param string $field
     * @param mixed  $values
     */
    protected function checkValue($field, $values)
    {
        FieldFilterHelper::checkArray($field, $values, 'family');

        foreach ($values as $value) {
            FieldFilterHelper::checkIdentifier($field, $value, 'family');
        }
    }
}
