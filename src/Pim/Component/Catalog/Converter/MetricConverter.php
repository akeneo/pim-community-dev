<?php

namespace Pim\Component\Catalog\Converter;

use Akeneo\Bundle\MeasureBundle\Convert\MeasureConverterInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\MetricInterface;
use Pim\Component\Catalog\Model\ProductInterface;

/**
 * Convert value into channel conversion unit if selected
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MetricConverter
{
    /** @var MeasureConverterInterface */
    protected $converter;

    /**
     * Constructor
     *
     * @param MeasureConverterInterface $converter
     */
    public function __construct(MeasureConverterInterface $converter)
    {
        $this->converter = $converter;
    }

    /**
     * Convert all the products metric values into the channel configured conversion units
     *
     * @param ProductInterface $product
     * @param ChannelInterface $channel
     */
    public function convert(ProductInterface $product, ChannelInterface $channel)
    {
        $channelUnits = $channel->getConversionUnits();
        foreach ($product->getValues() as $value) {
            $data = $value->getData();
            $attribute = $value->getAttribute();
            if ($data instanceof MetricInterface && isset($channelUnits[$attribute->getCode()])) {
                if (null === $data->getData()) {
                    return;
                }
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
