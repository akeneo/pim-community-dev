<?php

namespace Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Sorter\Attribute;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchQueryBuilder;
use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidDirectionException;
use Akeneo\Pim\Enrichment\Component\Product\Query\Sorter\AttributeSorterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Sorter\Directions;
use Akeneo\Pim\Enrichment\Component\Product\Validator\AttributeValidatorHelper;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;

/**
 * Abstract attribute sorter for an Elasticsearch query
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractAttributeSorter implements AttributeSorterInterface
{
    /** @var SearchQueryBuilder */
    protected $searchQueryBuilder;

    /** @var AttributeValidatorHelper  */
    protected $attrValidatorHelper;

    /** @var array */
    protected $supportedAttributeTypes;

    /**
     * @param AttributeValidatorHelper $attrValidatorHelper
     * @param array                    $supportedAttributeTypeCodes
     */
    public function __construct(
        AttributeValidatorHelper $attrValidatorHelper,
        array $supportedAttributeTypeCodes = []
    ) {
        $this->attrValidatorHelper = $attrValidatorHelper;
        $this->supportedAttributeTypes = $supportedAttributeTypeCodes;
    }

    /**
     * {@inheritdoc}
     *
     * About "unmapped_type", see https://www.elastic.co/guide/en/elasticsearch/reference/5.x/search-request-sort.html#_ignoring_unmapped_fields
     */
    public function addAttributeSorter(AttributeInterface $attribute, $direction, $locale = null, $channel = null)
    {
        if (null === $this->searchQueryBuilder) {
            throw new \LogicException('The search query builder is not initialized in the sorter.');
        }

        $this->checkLocaleAndChannel($attribute, $locale, $channel);

        $attributePath = $this->getAttributePath($attribute, $locale, $channel);

        $suffix = $this->getAttributePathSuffix();
        if (null !== $suffix) {
            $attributePath .= '.' . $suffix;
        }

        switch ($direction) {
            case Directions::ASCENDING:
                $sortClause = [
                    $attributePath => [
                        'order'   => 'ASC',
                        'missing' => '_last',
                        'unmapped_type' => 'long',
                    ],
                ];
                $this->searchQueryBuilder->addSort($sortClause);

                break;
            case Directions::DESCENDING:
                $sortClause = [
                    $attributePath => [
                        'order'   => 'DESC',
                        'missing' => '_last',
                        'unmapped_type' => 'long',
                    ],
                ];

                $this->searchQueryBuilder->addSort($sortClause);

                break;
            default:
                throw InvalidDirectionException::notSupported($direction, static::class);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supportsAttribute(AttributeInterface $attribute)
    {
        return in_array($attribute->getType(), $this->supportedAttributeTypes);
    }

    /**
     * {@inheritdoc}
     */
    public function setQueryBuilder($searchQueryBuilder)
    {
        if (!$searchQueryBuilder instanceof SearchQueryBuilder) {
            throw new \InvalidArgumentException(
                sprintf('Query builder should be an instance of "%s"', SearchQueryBuilder::class)
            );
        }

        $this->searchQueryBuilder = $searchQueryBuilder;
    }

    /**
     * TODO: TIP-706: Those util functions should definitely be refactored somewhere else
     *
     * @param AttributeInterface $attribute
     * @param string             $locale
     * @param string             $channel
     *
     * @return string
     */
    protected function getAttributePath(AttributeInterface $attribute, $locale, $channel)
    {
        $locale = (null === $locale) ? '<all_locales>' : $locale;
        $channel = (null === $channel) ? '<all_channels>' : $channel;

        return 'values.' . $attribute->getCode() . '-' . $attribute->getBackendType() . '.' . $channel . '.' . $locale;
    }

    /**
     * Check locale and scope are valid
     *
     * @param AttributeInterface $attribute
     * @param string             $locale
     * @param string             $channel
     *
     * @throws InvalidPropertyException
     */
    protected function checkLocaleAndChannel(AttributeInterface $attribute, $locale, $channel)
    {
        try {
            $this->attrValidatorHelper->validateLocale($attribute, $locale);
            $this->attrValidatorHelper->validateScope($attribute, $channel);
        } catch (\LogicException $e) {
            throw InvalidPropertyException::expectedFromPreviousException(
                $attribute->getCode(),
                static::class,
                $e
            );
        }
    }

    /**
     * Returns the extra suffix to add to the attribute path
     *
     * @return mixed
     */
    abstract protected function getAttributePathSuffix();
}
