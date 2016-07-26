<?php

namespace Pim\Bundle\ReferenceDataBundle\Doctrine\MongoDB\Filter;

use Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\AbstractAttributeFilter;
use Pim\Bundle\CatalogBundle\ProductQueryUtility;
use Pim\Bundle\ReferenceDataBundle\Doctrine\ReferenceDataIdResolver;
use Pim\Component\Catalog\Exception\InvalidArgumentException;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Query\Filter\AttributeFilterInterface;
use Pim\Component\Catalog\Query\Filter\FieldFilterHelper;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;
use Pim\Component\ReferenceData\ConfigurationRegistryInterface;

/**
 * Reference data filter
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReferenceDataFilter extends AbstractAttributeFilter implements AttributeFilterInterface
{
    /** @var ConfigurationRegistryInterface */
    protected $registry;

    /** @var ReferenceDataIdResolver */
    protected $idsResolver;

    /**
     * @param ChannelRepositoryInterface     $channelRepository
     * @param LocaleRepositoryInterface      $localeRepository
     * @param ConfigurationRegistryInterface $registry
     * @param ReferenceDataIdResolver        $idsResolver
     * @param array                          $supportedOperators
     */
    public function __construct(
        ChannelRepositoryInterface $channelRepository,
        LocaleRepositoryInterface $localeRepository,
        ConfigurationRegistryInterface $registry,
        ReferenceDataIdResolver $idsResolver,
        array $supportedOperators = []
    ) {
        parent::__construct($channelRepository, $localeRepository);

        $this->registry = $registry;
        $this->idsResolver = $idsResolver;
        $this->supportedOperators  = $supportedOperators;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsAttribute(AttributeInterface $attribute)
    {
        $referenceDataName = $attribute->getReferenceDataName();

        return null !== $referenceDataName && null !== $this->registry->get($referenceDataName) ? true : false;
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
        if (Operators::IS_EMPTY !== $operator) {
            $field = $options['field'];
            $this->checkValue($field, $value);

            if (FieldFilterHelper::CODE_PROPERTY === FieldFilterHelper::getProperty($field)) {
                $value = $this->valueCodesToIds($attribute, $value);
            }
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
     * Apply the filters to the query with the given operator
     *
     * @param array  $fields
     * @param string $operator
     * @param mixed  $value
     */
    protected function applyFilters(array $fields, $operator, $value)
    {
        if (Operators::IS_EMPTY === $operator) {
            foreach ($fields as $field) {
                $expr = $this->qb->expr()->field($field)->exists(false);
                $this->qb->addAnd($expr);
            }
        } else {
            foreach ($fields as $field) {
                $value = array_map('intval', $value);
                $expr = $this->qb->expr()->field($field)->in($value);
                $this->qb->addOr($expr);
            }
        }
    }

    /**
     * Check if value is valid
     *
     * @param string $field
     * @param mixed  $values
     */
    protected function checkValue($field, $values)
    {
        FieldFilterHelper::checkArray($field, $values, 'reference_data');

        foreach ($values as $value) {
            FieldFilterHelper::checkIdentifier($field, $value, 'reference_data');
        }
    }

    /**
     * @param AttributeInterface $attribute
     * @param string             $value
     *
     * @return int
     */
    protected function valueCodesToIds(AttributeInterface $attribute, $value)
    {
        try {
            $value = $this->idsResolver->resolve($attribute->getReferenceDataName(), $value);
        } catch (\LogicException $e) {
            throw InvalidArgumentException::validEntityCodeExpected(
                $attribute->getCode(),
                'code',
                $e->getMessage(),
                'setter',
                'reference data',
                implode(',', $value)
            );
        }

        return $value;
    }
}
