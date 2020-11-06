<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Attribute;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchQueryBuilder;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\AttributeFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Validator\ElasticsearchFilterValidator;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;

/**
 * Abstract attribute filter for Elasticsearch
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractAttributeFilter implements AttributeFilterInterface
{
    protected const ATTRIBUTES_FOR_THIS_LEVEL_ES_ID = 'attributes_for_this_level';
    protected const ATTRIBUTES_OF_ANCESTORS_ES_ID = 'attributes_of_ancestors';

    /** @var SearchQueryBuilder */
    protected $searchQueryBuilder = null;

    /** @var ElasticsearchFilterValidator */
    protected $filterValidator;

    /** @var string[] */
    protected $supportedAttributeTypes;

    /** @var array */
    protected $supportedOperators = [];

    /**
     * {@inheritdoc}
     */
    public function getAttributeTypes(): array
    {
        return $this->supportedAttributeTypes;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsAttribute(AttributeInterface $attribute): bool
    {
        return in_array($attribute->getType(), $this->supportedAttributeTypes);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsOperator(string $operator): bool
    {
        return in_array($operator, $this->supportedOperators);
    }

    /**
     * {@inheritdoc}
     */
    public function getOperators(): array
    {
        return $this->supportedOperators;
    }

    /**
     * {@inheritdoc}
     */
    public function setQueryBuilder(\Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchQueryBuilder $searchQueryBuilder)
    {
        if (!$searchQueryBuilder instanceof SearchQueryBuilder) {
            throw new \InvalidArgumentException(
                sprintf('Query builder should be an instance of "%s"', SearchQueryBuilder::class)
            );
        }

        $this->searchQueryBuilder = $searchQueryBuilder;
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
    protected function checkLocaleAndChannel(AttributeInterface $attribute, string $locale, string $channel)
    {
        try {
            $this->filterValidator->validateLocaleForAttribute($attribute->getCode(), $locale);
            $this->filterValidator->validateChannelForAttribute($attribute->getCode(), $channel);
        } catch (\LogicException $e) {
            throw InvalidPropertyException::expectedFromPreviousException(
                $attribute->getCode(),
                static::class,
                $e
            );
        }
    }

    /**
     * Calculates the ES path to a product value indexed in ES.
     *
     * TODO: TIP-706 - All this logic should be done somewhere else
     *
     * @param AttributeInterface $attribute
     * @param string             $locale
     * @param string             $channel
     */
    protected function getAttributePath(AttributeInterface $attribute, string $locale, string $channel): string
    {
        $locale = (null === $locale) ? '<all_locales>' : $locale;
        $channel = (null === $channel) ? '<all_channels>' : $channel;

        return 'values.' . $attribute->getCode() . '-' . $attribute->getBackendType() . '.' . $channel . '.' . $locale;
    }
}
