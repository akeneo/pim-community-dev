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
 * Media filter
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MediaFilter extends AbstractAttributeFilter implements AttributeFilterInterface
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
        if (Operators::IS_EMPTY !== $operator && Operators::IS_NOT_EMPTY !== $operator) {
            $this->checkValue($attribute, $value);
        }

        $normalizedFields = $this->getNormalizedValueFieldsFromAttribute($attribute, $locale, $scope);
        $fields = [];

        foreach ($normalizedFields as $normalizedField) {
            $fields[] = sprintf('%s.%s.originalFilename', ProductQueryUtility::NORMALIZED_FIELD, $normalizedField);
        }

        $this->applyFilters($fields, $operator, $value);

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
            case Operators::NOT_EQUAL:
                foreach ($fields as $field) {
                    $this->qb->field($field)->exists(true);
                    $this->qb->field($field)->notEqual($value);
                }
                break;
            case Operators::IS_NOT_EMPTY:
                foreach ($fields as $field) {
                    $expr = $this->qb->expr()->field($field)->exists(true);
                    $this->qb->addOr($expr);
                }
                break;
            case Operators::IS_EMPTY:
                foreach ($fields as $field) {
                    $expr = $this->qb->expr()->field($field)->exists(false);
                    $this->qb->addAnd($expr);
                }
                break;
            default:
                $value = $this->prepareValue($operator, $value);
                foreach ($fields as $field) {
                    $expr = $this->qb->expr()->field($field)->equals($value);
                    $this->qb->addOr($expr);
                }
        }
    }

    /**
     * Prepare value of the filter
     *
     * @param string|array $operator
     * @param string|array $value
     *
     * @return string
     */
    protected function prepareValue($operator, $value)
    {
        switch ($operator) {
            case Operators::EQUALS:
                $value = new \MongoRegex(sprintf('/^%s$/i', $value));
                break;
            case Operators::STARTS_WITH:
                $value = new \MongoRegex(sprintf('/^%s/i', $value));
                break;
            case Operators::ENDS_WITH:
                $value = new \MongoRegex(sprintf('/%s$/i', $value));
                break;
            case Operators::CONTAINS:
                $value = new \MongoRegex(sprintf('/%s/i', $value));
                break;
            case Operators::DOES_NOT_CONTAIN:
                $value = new \MongoRegex(sprintf('/^((?!%s).)*$/i', $value));
                break;
        }

        return $value;
    }

    /**
     * @param AttributeInterface $attribute
     * @param mixed              $value
     */
    protected function checkValue(AttributeInterface $attribute, $value)
    {
        if (!is_string($value)) {
            throw InvalidArgumentException::stringExpected(
                $attribute->getCode(),
                'filter',
                'media',
                gettype($value)
            );
        }
    }
}
