<?php

namespace Pim\Bundle\ImportExportBundle\Converter;

use Oro\Bundle\MeasureBundle\Convert\MeasureConverter;
use Pim\Bundle\CatalogBundle\Model\Metric;
use Pim\Bundle\CatalogBundle\Entity\Channel;

/**
 * Convert value into channel conversion unit if selected
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MetricConverter
{
    /** @var MeasureConverter */
    protected $converter;

    /**
     * Constructor
     *
     * @param MeasureConverter $converter
     */
    public function __construct(MeasureConverter $converter)
    {
        $this->converter = $converter;
    }

    /**
     * Convert all the products metric values into the channel configured conversion units
     *
     * @param array   $products
     * @param Channel $channel
     */
    public function convert(array $products, Channel $channel)
    {
        $channelUnits = $channel->getConversionUnits();
        foreach ($products as $product) {
            foreach ($product->getValues() as $value) {
                $data = $value->getData();
                $attribute = $value->getAttribute();
                if ($data instanceof Metric && isset($channelUnits[$attribute->getCode()])) {
                    $channelUnit = $channelUnits[$attribute->getCode()];
                    $this->converter->setFamily($data->getFamily());
                    $data->setData(
                        $this->converter->convert($data->getUnit(), $channelUnit, $data->getData())
                    );
                    $data->setUnit($channelUnit);
                }
            }
        }
    }
}
