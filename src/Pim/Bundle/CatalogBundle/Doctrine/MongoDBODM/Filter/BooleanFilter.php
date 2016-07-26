<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter;

use Pim\Bundle\CatalogBundle\ProductQueryUtility;
use Pim\Component\Catalog\Exception\InvalidArgumentException;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Query\Filter\AttributeFilterInterface;
use Pim\Component\Catalog\Query\Filter\FieldFilterInterface;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;

/**
 * Boolean filter
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BooleanFilter extends AbstractAttributeFilter implements FieldFilterInterface, AttributeFilterInterface
{
    /** @var array */
    protected $supportedFields;

    /**
     * @param ChannelRepositoryInterface $channelRepository
     * @param LocaleRepositoryInterface  $localeRepository
     * @param array                      $supportedAttributeTypes
     * @param array                      $supportedFields
     * @param array                      $supportedOperators
     */
    public function __construct(
        ChannelRepositoryInterface $channelRepository,
        LocaleRepositoryInterface $localeRepository,
        array $supportedAttributeTypes = [],
        array $supportedFields = [],
        array $supportedOperators = []
    ) {
        parent::__construct($channelRepository, $localeRepository);

        $this->supportedAttributeTypes = $supportedAttributeTypes;
        $this->supportedFields = $supportedFields;
        $this->supportedOperators  = $supportedOperators;
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
    public function getFields()
    {
        return $this->supportedFields;
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
        if (!is_bool($value)) {
            throw InvalidArgumentException::booleanExpected(
                $attribute->getCode(),
                'filter',
                'boolean',
                gettype($value)
            );
        }

        $normalizedFields = $this->getNormalizedValueFieldsFromAttribute($attribute, $locale, $scope);
        $fields = [];

        foreach ($normalizedFields as $normalizedField) {
            $fields[] = sprintf('%s.%s', ProductQueryUtility::NORMALIZED_FIELD, $normalizedField);
        }

        $this->applyFilters($fields, $operator, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldFilter($field, $operator, $value, $locale = null, $scope = null, $options = [])
    {
        if (!is_bool($value)) {
            throw InvalidArgumentException::booleanExpected($field, 'filter', 'boolean', gettype($value));
        }

        $field = sprintf('%s.%s', ProductQueryUtility::NORMALIZED_FIELD, $field);
        $this->applyFilter($field, $operator, $value);

        return $this;
    }

    /**
     * Apply the filters to the query with the given operator
     *
     * @param array  $fields
     * @param string $operator
     * @param mixed  $value
     */
    protected function applyFilters(array $fields, $operator, $value)
    {
        switch ($operator) {
            case Operators::EQUALS:
                foreach ($fields as $field) {
                    $expr = $this->qb->expr()->field($field)->equals($value);
                    $this->qb->addOr($expr);
                }
                break;
            case Operators::NOT_EQUAL:
                foreach ($fields as $field) {
                    $this->qb->field($field)->exists(true);
                    $this->qb->field($field)->notEqual($value);
                }
                break;
        }
    }

    /**
     * @param string $field
     * @param string $operator
     * @param bool   $value
     */
    protected function applyFilter($field, $operator, $value)
    {
        switch ($operator) {
            case Operators::EQUALS:
                $this->qb->field($field)->equals($value);
                break;
            case Operators::NOT_EQUAL:
                $this->qb->field($field)->exists(true);
                $this->qb->field($field)->notEqual($value);
                break;
        }
    }
}
