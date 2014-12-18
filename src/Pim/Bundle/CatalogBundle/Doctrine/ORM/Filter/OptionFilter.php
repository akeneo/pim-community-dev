<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter;

use Pim\Bundle\CatalogBundle\Doctrine\InvalidArgumentException;
use Pim\Bundle\CatalogBundle\Doctrine\Query\AttributeFilterInterface;
use Pim\Bundle\CatalogBundle\Doctrine\Query\FieldFilterHelper;
use Pim\Bundle\CatalogBundle\Doctrine\Query\Operators;
use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeOptionRepository;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;

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

    /** @var AttributeOptionRepository */
    protected $attributeOptionRepo;

    /**
     * Instanciate the base filter
     *
     * @param AttributeOptionRepository $attributeOptionRepo
     * @param array                     $supportedAttributes
     * @param array                     $supportedOperators
     */
    public function __construct(
        AttributeOptionRepository $attributeOptionRepo,
        array $supportedAttributes = [],
        array $supportedOperators = []
    ) {
        $this->supportedAttributes = $supportedAttributes;
        $this->supportedOperators  = $supportedOperators;
        $this->attributeOptionRepo = $attributeOptionRepo;
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
        $field = $options['field'];

        $this->checkValue($field, $operator, $value);

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
                $value = $this->getIdsFromCodes($value);
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
     * @param string             $operator
     * @param mixed              $value
     */
    protected function checkValue($field, $operator, $value)
    {
        if (!is_array($value) && Operators::IS_EMPTY !== $operator) {
            throw InvalidArgumentException::arrayExpected(FieldFilterHelper::getCode($field), 'filter', 'option');
        }

        if (Operators::IS_EMPTY !== $operator) {
            foreach ($value as $entity) {
                if ((
                        FieldFilterHelper::hasProperty($field) &&
                        FieldFilterHelper::getProperty($field) === 'id' &&
                        !is_numeric($entity)
                    ) ||
                    (
                        !FieldFilterHelper::hasProperty($field) &&
                        !is_numeric($entity)
                    )
                ) {
                    throw InvalidArgumentException::integerExpected($field, 'filter', 'option');
                } elseif (FieldFilterHelper::hasProperty($field) &&
                    FieldFilterHelper::getProperty($field) !== 'id'
                    && !is_string($entity)
                ) {
                    throw InvalidArgumentException::stringExpected($field, 'filter', 'option');
                }
            }
        }
    }

    protected function getIdsFromCodes($attributeOptionCodes)
    {
        $attributeOptionIds = [];

        foreach ($attributeOptionCodes as $code) {
            //TODO catch exception if the code does not exists
            $attributeOptionIds[] = $this->attributeOptionRepo->findOneByCode($code)->getId();
        }

        return $attributeOptionIds;
    }
}
