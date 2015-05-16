<?php

namespace Pim\Bundle\BaseConnectorBundle\Reader\File;

use Pim\Component\Connector\ArrayConverter\Flat\ProductAssociationFieldResolver;
use Pim\Component\Connector\ArrayConverter\Flat\ProductAttributeFieldExtractor;

/**
 * Association csv reader
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CsvAssociationReader extends CsvReader
{
    /** @var ProductAssociationFieldResolver */
    protected $assocFieldResolver;

    /**
     * @param ProductAssociationFieldResolver $assocFieldResolver
     */
    public function __construct(ProductAssociationFieldResolver $assocFieldResolver)
    {
        $this->assocFieldResolver = $assocFieldResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        $data = parent::read();
        if (null === $data) {
            return;
        }

        // Get association field names and add associations
        $assocFieldNames  = $this->assocFieldResolver->resolveAssociationFields();
        $associations = [
            'product'      => $data,
            'associations' => []
        ];
        foreach ($assocFieldNames as $assocFieldName) {
            if (isset($data[$assocFieldName])) {
                if (strlen($data[$assocFieldName]) > 0) {
                    list($assocTypeCode, $part) = explode(ProductAttributeFieldExtractor::FIELD_SEPARATOR, $assocFieldName);

                    $associations['associations'][] = [
                        'associated_items'      => explode(ProductAttributeFieldExtractor::ARRAY_SEPARATOR, $data[$assocFieldName]),
                        'association_type_code' => $assocTypeCode,
                        'item_type'             => $part
                    ];
                }
            }
        }

        return $associations;
    }
}
