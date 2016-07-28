<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter;

use Pim\Bundle\CatalogBundle\ProductQueryUtility;
use Pim\Component\Catalog\Exception\InvalidArgumentException;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Query\Filter\AttributeFilterInterface;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;
use Pim\Component\Catalog\Repository\CurrencyRepositoryInterface;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;

/**
 * Price filter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PriceFilter extends AbstractAttributeFilter implements AttributeFilterInterface
{
    /** @var CurrencyRepositoryInterface */
    protected $currencyRepository;

    /**
     * @param ChannelRepositoryInterface  $channelRepository
     * @param LocaleRepositoryInterface   $localeRepository
     * @param CurrencyRepositoryInterface $currencyRepository
     * @param array                       $supportedAttributeTypes
     * @param array                       $supportedOperators
     */
    public function __construct(
        ChannelRepositoryInterface $channelRepository,
        LocaleRepositoryInterface $localeRepository,
        CurrencyRepositoryInterface $currencyRepository,
        array $supportedAttributeTypes = [],
        array $supportedOperators = []
    ) {
        parent::__construct($channelRepository, $localeRepository);

        $this->currencyRepository      = $currencyRepository;
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
            $value['data'] = (float) $value['data'];
        }

        $normalizedFields = $this->getNormalizedValueFieldsFromAttribute($attribute, $locale, $scope);
        $fields = [];
        foreach ($normalizedFields as $normalizedField) {
            $fields[] = sprintf(
                '%s.%s.%s.data',
                ProductQueryUtility::NORMALIZED_FIELD,
                $normalizedField,
                $value['currency']
            );
        }

        $this->applyFilters($fields, $operator, $value['data']);

        return $this;
    }

    /**
     * Apply the filter to the query with the given operator
     *
     * @param array  $fields
     * @param string $operator
     * @param float  $data
     */
    protected function applyFilters(array $fields, $operator, $data)
    {
        foreach ($fields as $field) {
            switch ($operator) {
                case Operators::EQUALS:
                    $expr = $this->qb->expr()->field($field)->equals($data);
                    $this->qb->addOr($expr);
                    break;
                case Operators::NOT_EQUAL:
                    $this->qb
                        ->addOr($this->qb->expr()->field($field)->exists(true))
                        ->addOr($this->qb->expr()->field($field)->notEqual($data));
                    break;
                case Operators::LOWER_THAN:
                    $expr = $this->qb->expr()->field($field)->lt($data);
                    $this->qb->addOr($expr);
                    break;
                case Operators::LOWER_OR_EQUAL_THAN:
                    $expr = $this->qb->expr()->field($field)->lte($data);
                    $this->qb->addOr($expr);
                    break;
                case Operators::GREATER_THAN:
                    $expr = $this->qb->expr()->field($field)->gt($data);
                    $this->qb->addOr($expr);
                    break;
                case Operators::GREATER_OR_EQUAL_THAN:
                    $expr = $this->qb->expr()->field($field)->gte($data);
                    $this->qb->addOr($expr);
                    break;
                case Operators::IS_EMPTY:
                    $expr = $this->qb->expr()->field($field)->exists(false);
                    $this->qb->addOr($expr);
                    break;
                case Operators::IS_NOT_EMPTY:
                    $expr = $this->qb->expr()->field($field)->exists(true);
                    $this->qb->addOr($expr);
                    break;
            }
        }
    }

    /**
     * @param AttributeInterface $attribute
     * @param mixed              $data
     */
    protected function checkValue(AttributeInterface $attribute, $data)
    {
        if (!is_array($data)) {
            throw InvalidArgumentException::arrayExpected($attribute->getCode(), 'filter', 'price', gettype($data));
        }

        if (!array_key_exists('data', $data)) {
            throw InvalidArgumentException::arrayKeyExpected(
                $attribute->getCode(),
                'data',
                'filter',
                'price',
                print_r($data, true)
            );
        }

        if (!array_key_exists('currency', $data)) {
            throw InvalidArgumentException::arrayKeyExpected(
                $attribute->getCode(),
                'currency',
                'filter',
                'price',
                print_r($data, true)
            );
        }

        if (null !== $data['data'] && !is_int($data['data']) && !is_float($data['data'])) {
            throw InvalidArgumentException::arrayNumericKeyExpected(
                $attribute->getCode(),
                'data',
                'filter',
                'price',
                gettype($data['data'])
            );
        }

        if (!is_string($data['currency'])) {
            throw InvalidArgumentException::arrayStringKeyExpected(
                $attribute->getCode(),
                'currency',
                'filter',
                'price',
                gettype($data['currency'])
            );
        }

        if (!in_array($data['currency'], $this->currencyRepository->getActivatedCurrencyCodes())) {
            throw InvalidArgumentException::arrayInvalidKey(
                $attribute->getCode(),
                'currency',
                'The currency does not exist',
                'filter',
                'price',
                $data['currency']
            );
        }
    }
}
