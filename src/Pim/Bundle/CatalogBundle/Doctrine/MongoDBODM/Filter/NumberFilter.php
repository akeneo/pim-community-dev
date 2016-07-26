<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter;

use Pim\Bundle\CatalogBundle\ProductQueryUtility;
use Pim\Component\Catalog\Exception\InvalidArgumentException;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Query\Filter\AttributeFilterInterface;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;

/**
 * Number filter
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NumberFilter extends AbstractAttributeFilter implements AttributeFilterInterface
{
    /**
     * @param ChannelRepositoryInterface $channelRepository
     * @param LocaleRepositoryInterface  $localeRepository
     * @param array                      $supportedAttributeTypes
     * @param array                      $supportedOperators
     */
    public function __construct(
        ChannelRepositoryInterface $channelRepository,
        LocaleRepositoryInterface $localeRepository,
        array $supportedAttributeTypes = [],
        array $supportedOperators = []
    ) {
        parent::__construct($channelRepository, $localeRepository);

        $this->supportedAttributeTypes = $supportedAttributeTypes;
        $this->supportedOperators      = $supportedOperators;
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
        if (null !== $value && !is_numeric($value)) {
            throw InvalidArgumentException::numericExpected($attribute->getCode(), 'filter', 'number', gettype($value));
        }

        $normalizedFields = $this->getNormalizedValueFieldsFromAttribute($attribute, $locale, $scope);
        $fields = [];
        foreach ($normalizedFields as $normalizedField) {
            $fields[] = sprintf(
                 '%s.%s.id',
                 ProductQueryUtility::NORMALIZED_FIELD,
                $normalizedField
            );
        }

        $this->applyFilters($fields, $operator, $value);

        return $this;
    }

    /**
     * Apply the filter to the query with the given operator
     *
     * @param array    $fields
     * @param string   $operator
     * @param null|int $value
     */
    protected function applyFilters(array $fields, $operator, $value)
    {
        foreach ($fields as $field) {
            switch ($operator) {
                case Operators::IS_EMPTY:
                    $expr = $this->qb->expr()->field($field)->exists(false);
                    $this->qb->addOr($expr);
                    break;
                case Operators::IS_NOT_EMPTY:
                    $expr = $this->qb->expr()->field($field)->exists(true);
                    $this->qb->addOr($expr);
                    break;
                case Operators::EQUALS:
                    $expr = $this->qb->expr()->field($field)->equals($value);
                    $this->qb->addOr($expr);
                    break;
                case Operators::NOT_EQUAL:
                    $exprExists = $this->qb->expr()->field($field)->exists(true);
                    $exprNotEqual = $this->qb->expr()->field($field)->notEqual($value);
                    $this->qb->addOr($exprExists)->addOr($exprNotEqual);
                    break;
                case Operators::LOWER_THAN:
                    $expr = $this->qb->expr()->field($field)->lt($value);
                    $this->qb->addOr($expr);
                    break;
                case Operators::GREATER_THAN:
                    $expr = $this->qb->expr()->field($field)->gt($value);
                    $this->qb->addOr($expr);
                    break;
                case Operators::LOWER_OR_EQUAL_THAN:
                    $expr = $this->qb->expr()->field($field)->lte($value);
                    $this->qb->addOr($expr);
                    break;
                case Operators::GREATER_OR_EQUAL_THAN:
                    $expr = $this->qb->expr()->field($field)->gte($value);
                    $this->qb->addOr($expr);
                    break;
            }
        }
    }
}
