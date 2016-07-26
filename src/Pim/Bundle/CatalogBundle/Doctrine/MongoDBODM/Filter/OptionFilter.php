<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter;

use Pim\Bundle\CatalogBundle\Doctrine\Common\Filter\ObjectIdResolverInterface;
use Pim\Bundle\CatalogBundle\ProductQueryUtility;
use Pim\Component\Catalog\Exception\InvalidArgumentException;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Query\Filter\AttributeFilterInterface;
use Pim\Component\Catalog\Query\Filter\FieldFilterHelper;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Simple option filter for MongoDB implementation
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OptionFilter extends AbstractAttributeFilter implements AttributeFilterInterface
{
    /** @var ObjectIdResolverInterface */
    protected $objectIdResolver;

    /** @var OptionsResolver */
    protected $resolver;

    /**
     * @param ChannelRepositoryInterface $channelRepository
     * @param LocaleRepositoryInterface  $localeRepository
     * @param ObjectIdResolverInterface  $objectIdResolver
     * @param array                      $supportedAttributeTypes
     * @param array                      $supportedOperators
     */
    public function __construct(
        ChannelRepositoryInterface $channelRepository,
        LocaleRepositoryInterface $localeRepository,
        ObjectIdResolverInterface $objectIdResolver,
        array $supportedAttributeTypes = [],
        array $supportedOperators = []
    ) {
        parent::__construct($channelRepository, $localeRepository);

        $this->objectIdResolver        = $objectIdResolver;
        $this->supportedAttributeTypes = $supportedAttributeTypes;
        $this->supportedOperators      = $supportedOperators;

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
        try {
            $options = $this->resolver->resolve($options);
        } catch (\Exception $e) {
            throw InvalidArgumentException::expectedFromPreviousException(
                $e,
                $attribute->getCode(),
                'filter',
                'option'
            );
        }

        if (Operators::IS_EMPTY !== $operator && Operators::IS_NOT_EMPTY !== $operator) {
            $this->checkValue($options['field'], $value);

            if (FieldFilterHelper::CODE_PROPERTY === FieldFilterHelper::getProperty($options['field'])) {
                $value = $this->objectIdResolver->getIdsFromCodes('option', $value, $attribute);
            } else {
                $value = array_map('intval', $value);
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
     * Check if value is valid
     *
     * @param string $field
     * @param mixed  $values
     */
    protected function checkValue($field, $values)
    {
        FieldFilterHelper::checkArray($field, $values, 'option');

        foreach ($values as $value) {
            FieldFilterHelper::checkIdentifier($field, $value, 'option');
        }
    }

    /**
     * Apply the filter to the query with the given operator
     *
     * @param array  $fields
     * @param string $operator
     * @param mixed  $value
     */
    protected function applyFilters(array $fields, $operator, $value)
    {
        foreach ($fields as $field) {
            switch ($operator) {
                case Operators::IN_LIST:
                    $expr = $this->qb->expr()->field($field)->in($value);
                    $this->qb->addOr($expr); // TODO check with PO
                    break;
                case Operators::NOT_IN_LIST:
                    $this->qb
                        ->addOr($this->qb->expr()->field($field)->exists(true))
                        ->addOr($this->qb->expr()->field($field)->notIn($value));
                    break;
                case Operators::IS_EMPTY:
                    $expr = $this->qb->expr()->field($field)->exists(false);
                    $this->qb->addOr($expr); // TODO check with PO
                    break;
                case Operators::IS_NOT_EMPTY:
                    $expr = $this->qb->expr()->field($field)->exists(true);
                    $this->qb->addOr($expr); // TODO check with PO
                    break;
            }
        }
    }

    /**
     * Configure the option resolver
     *
     * @param OptionsResolver $resolver
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['field']);
        $resolver->setDefined(['locale', 'scope']);
    }
}
