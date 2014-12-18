<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter;

use Doctrine\MongoDB\Query\Expr;
use Pim\Bundle\CatalogBundle\Doctrine\InvalidArgumentException;
use Pim\Bundle\CatalogBundle\Doctrine\Query\FieldFilterHelper;
use Pim\Bundle\CatalogBundle\Doctrine\Query\Operators;
use Pim\Bundle\CatalogBundle\Doctrine\Query\FieldFilterInterface;
use Pim\Bundle\CatalogBundle\Doctrine\Common\EntityIdResolverInterface;

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

    /** @var EntityIdResolverInterface */
    protected $entityIdResolver;

    /**
     * Instanciate the filter
     *
     * @param EntityIdResolverInterface $entityIdResolver
     * @param array                     $supportedFields
     * @param array                     $supportedOperators
     */
    public function __construct(
        EntityIdResolverInterface $entityIdResolver,
        array $supportedFields = [],
        array $supportedOperators = []
    ) {
        $this->entityIdResolver   = $entityIdResolver;
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
                $value = $this->entityIdResolver->getIdsFromCodes('option', $value);
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
                $expr = new Expr();
                $this->qb->addAnd(
                    $expr->field($fieldCode)->exists(false)
                );
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
