<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter;

use Pim\Bundle\CatalogBundle\Doctrine\InvalidArgumentException;
use Pim\Bundle\CatalogBundle\Doctrine\Query\AttributeFilterInterface;
use Pim\Bundle\CatalogBundle\Doctrine\Query\FieldFilterHelper;
use Pim\Bundle\CatalogBundle\Doctrine\Query\Operators;
use Pim\Bundle\CatalogBundle\Doctrine\Common\ObjectIdResolverInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;

/**
 * Filtering by multi option backend type
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OptionsFilter extends AbstractFilter implements AttributeFilterInterface
{
    /** @var array */
    protected $supportedAttributes;

    /** @var ObjectIdResolverInterface */
    protected $entityIdResolver;

    /**
     * Instanciate the filter
     *
     * @param ObjectIdResolverInterface $entityIdResolver
     * @param array                     $supportedAttributes
     * @param array                     $supportedOperators
     */
    public function __construct(
        ObjectIdResolverInterface $entityIdResolver,
        array $supportedAttributes = [],
        array $supportedOperators = []
    ) {
        $this->entityIdResolver    = $entityIdResolver;
        $this->supportedAttributes = $supportedAttributes;
        $this->supportedOperators  = $supportedOperators;
    }

    /**
     * {@inheritdoc}
     */
    public function addAttributeFilter(
        AttributeInterface $attribute,
        $operator,
        $value,
        $locale = null,
        $scope = null,
        $options = []
    ) {
        if ($operator != Operators::IS_EMPTY) {
            $this->checkValue($options['field'], $value);
        }

        $joinAlias    = 'filter'.$attribute->getCode();
        $joinAliasOpt = 'filterO'.$attribute->getCode();
        $backendField = sprintf('%s.%s', $joinAliasOpt, 'id');

        if (Operators::IS_EMPTY === $operator) {
            $this->qb->leftJoin(
                $this->qb->getRootAlias().'.values',
                $joinAlias,
                'WITH',
                $this->prepareAttributeJoinCondition($attribute, $joinAlias, $locale, $scope)
            );

            $this->qb
                ->leftJoin($joinAlias .'.'. $attribute->getBackendType(), $joinAliasOpt)
                ->andWhere($this->qb->expr()->isNull($backendField));
        } else {
            if (FieldFilterHelper::getProperty($options['field']) === FieldFilterHelper::CODE_PROPERTY) {
                $value = $this->entityIdResolver->getIdsFromCodes('option', $value);
            }

            $this->qb
                ->innerJoin(
                    $this->qb->getRootAlias().'.values',
                    $joinAlias,
                    'WITH',
                    $this->prepareAttributeJoinCondition($attribute, $joinAlias, $locale, $scope)
                )
                ->innerJoin(
                    $joinAlias .'.'. $attribute->getBackendType(),
                    $joinAliasOpt,
                    'WITH',
                    $this->qb->expr()->in($backendField, $value)
                );
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsAttribute(AttributeInterface $attribute)
    {
        return in_array($attribute->getAttributeType(), $this->supportedAttributes);
    }

    /**
     * Check if value is valid
     *
     * @param AttributeInterface $attribute
     * @param mixed              $value
     */
    protected function checkValue($field, $values)
    {
        FieldFilterHelper::checkArray($field, $values, 'option');

        foreach ($values as $value) {
            FieldFilterHelper::checkIdentifier($field, $value, 'option');
        }
    }
}
