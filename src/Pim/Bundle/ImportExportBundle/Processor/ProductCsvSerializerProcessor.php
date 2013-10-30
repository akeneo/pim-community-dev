<?php

namespace Pim\Bundle\ImportExportBundle\Processor;

/**
 * Product serializer into csv processor
 *
 * This processor stores the media of the products among
 * with the serialized products in order to write them later
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductCsvSerializerProcessor extends HeterogeneousCsvSerializerProcessor
{
    /**
     * {@inheritdoc}
     */
    public function process($products)
    {
        $csv = parent::process($products);

        if (!is_array($products)) {
            $products = array($products);
        }

        $media = array();
        foreach ($products as $product) {
            $media = array_merge($product->getMedia(), $media);
        }

        return array(
            'entry' => $csv,
            'media' => $media
        );
    }
}
