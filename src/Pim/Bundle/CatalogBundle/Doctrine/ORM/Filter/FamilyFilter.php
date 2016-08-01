<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter;

use Pim\Bundle\CatalogBundle\Doctrine\Common\Filter\ObjectIdResolverInterface;
use Pim\Component\Catalog\Exception\InvalidArgumentException;
use Pim\Component\Catalog\Exception\ObjectNotFoundException;
use Pim\Component\Catalog\Query\Filter\FieldFilterHelper;
use Pim\Component\Catalog\Query\Filter\FieldFilterInterface;
use Pim\Component\Catalog\Query\Filter\Operators;

/**
 * Family filter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyFilter extends AbstractFieldFilter implements FieldFilterInterface
{
    /** @var ObjectIdResolverInterface */
    protected $objectIdResolver;

    /**
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
    public function addFieldFilter($field, $operator, $value, $locale = null, $scope = null, $options = [])
    {
        if (Operators::IS_EMPTY !== $operator && Operators::IS_NOT_EMPTY !== $operator) {
            $this->checkValue($field, $value);

            if (FieldFilterHelper::getProperty($field) === FieldFilterHelper::CODE_PROPERTY) {
                try {
                    $value = $this->objectIdResolver->getIdsFromCodes('family', $value);
                } catch (ObjectNotFoundException $e) {
                    throw InvalidArgumentException::validEntityCodeExpected(
                        $field,
                        'code',
                        $e->getMessage(),
                        'filter',
                        'family',
                        implode(', ', $value)
                    );
                }
            }
        }

        $rootAlias   = $this->qb->getRootAlias();
        $entityAlias = $this->getUniqueAlias('filter' . FieldFilterHelper::getCode($field));
        $this->qb->leftJoin($rootAlias . '.' . FieldFilterHelper::getCode($field), $entityAlias);

        switch ($operator) {
            case Operators::IN_LIST:
                $this->qb->andWhere(
                    $this->qb->expr()->in($entityAlias . '.id', $value)
                );
                break;
            case Operators::NOT_IN_LIST:
                $this->qb->andWhere(
                    $this->qb->expr()->orX(
                        $this->qb->expr()->notIn($entityAlias . '.id', $value),
                        $this->qb->expr()->isNull($entityAlias . '.id')
                    )
                );
                break;
            case Operators::IS_EMPTY:
                $this->qb->andWhere(
                    $this->qb->expr()->isNull($entityAlias . '.id')
                );
                break;
            case Operators::IS_NOT_EMPTY:
                $this->qb->andWhere($this->qb->expr()->isNotNull($entityAlias . '.id'));
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
