<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter;

use Pim\Bundle\CatalogBundle\Doctrine\Common\ObjectIdResolverInterface;
use Pim\Bundle\CatalogBundle\Doctrine\InvalidArgumentException;
use Pim\Bundle\CatalogBundle\Doctrine\Query\AttributeFilterInterface;
use Pim\Bundle\CatalogBundle\Doctrine\Query\FieldFilterHelper;
use Pim\Bundle\CatalogBundle\Doctrine\Query\Operators;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Filtering by simple option backend type
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OptionFilter extends AbstractFilter implements AttributeFilterInterface
{
    /** @var array */
    protected $supportedAttributes;

    /** @var ObjectIdResolverInterface */
    protected $objectIdResolver;

    /** @var OptionsResolver */
    protected $resolver;

    /**
     * Instanciate the base filter
     *
     * @param ObjectIdResolverInterface $objectIdResolver
     * @param array                     $supportedAttributes
     * @param array                     $supportedOperators
     */
    public function __construct(
        ObjectIdResolverInterface $objectIdResolver,
        array $supportedAttributes = [],
        array $supportedOperators = []
    ) {
        $this->objectIdResolver    = $objectIdResolver;
        $this->supportedAttributes = $supportedAttributes;
        $this->supportedOperators  = $supportedOperators;

        $this->resolver = new OptionsResolver();
        $this->configureOptions($this->resolver);
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
        $options = $this->resolver->resolve($options);

        $field = $options['field'];

        if (Operators::IS_EMPTY !== $operator) {
            $this->checkValue($field, $value);
        }

        $joinAlias = 'filter'.$attribute->getCode();

        // prepare join value condition
        $optionAlias = $joinAlias .'.option';

        if (Operators::IS_EMPTY === $operator) {
            $this->qb->leftJoin(
                $this->qb->getRootAlias().'.values',
                $joinAlias,
                'WITH',
                $this->prepareAttributeJoinCondition($attribute, $joinAlias, $locale, $scope)
            );

            $this->qb->andWhere($this->qb->expr()->isNull($optionAlias));
        } else {
            // inner join to value
            $condition = $this->prepareAttributeJoinCondition($attribute, $joinAlias, $locale, $scope);

            if (FieldFilterHelper::getProperty($field) === FieldFilterHelper::CODE_PROPERTY) {
                $value = $this->objectIdResolver->getIdsFromCodes('option', $value);
            }

            $condition .= ' AND ( '. $this->qb->expr()->in($optionAlias, $value) .' ) ';

            $this->qb->innerJoin(
                $this->qb->getRootAlias().'.values',
                $joinAlias,
                'WITH',
                $condition
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

    /**
     * Configure the option resolver
     * @param OptionsResolver $resolver
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('field');
    }
}
