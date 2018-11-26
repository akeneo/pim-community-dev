<?php

namespace Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Sorter;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Sorter\Field\BaseFieldSorter;
use Akeneo\Pim\Enrichment\Component\Product\Query\Sorter\AttributeSorterInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;

/**
 * Identifier sorter for an Elasticsearch query.
 *
 * As the identifier product value is not indexed in ES. Whenever you want to sort on the attribute identifier 'sku'
 * or on the field 'identifier', the PQB will use this class which sorts on the field identifier.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IdentifierSorter extends BaseFieldSorter implements AttributeSorterInterface
{
    /** @var array */
    protected $supportedAttributes;

    /**
     * @param array $supportedFields
     * @param array $supportedAttributes
     */
    public function __construct(array $supportedFields = [], array $supportedAttributes = [])
    {
        parent::__construct($supportedFields);

        $this->supportedAttributes = $supportedAttributes;
    }

    /**
     * {@inheritdoc}
     */
    public function addAttributeSorter(AttributeInterface $attribute, $direction, $locale = null, $channel = null)
    {
        $this->addFieldSorter('identifier', $direction);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsAttribute(AttributeInterface $attribute)
    {
        return in_array($attribute->getType(), $this->supportedAttributes);
    }
}
