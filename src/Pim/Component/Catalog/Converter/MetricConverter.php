<?php

namespace Pim\Component\Catalog\Converter;

use Akeneo\Bundle\MeasureBundle\Convert\MeasureConverter;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;
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
    /** @var MeasureConverter */
    protected $converter;

    /** @var ProductBuilderInterface */
    protected $productBuilder;

    /**
     * @param MeasureConverter        $converter
     * @param ProductBuilderInterface $productBuilder
     */
    public function __construct(MeasureConverter $converter, ProductBuilderInterface $productBuilder)
    {
        $this->converter = $converter;
        $this->productBuilder = $productBuilder;
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
                    continue;
                }

                $measureFamily = $data->getFamily();
                $channelUnit = $channelUnits[$attribute->getCode()];
                $amount = $this->converter
                    ->setFamily($measureFamily)
                    ->convert($data->getUnit(), $channelUnit, $data->getData());

                $this->productBuilder->addOrReplaceProductValue(
                    $product,
                    $attribute,
                    $value->getLocale(),
                    $value->getScope(),
                    ['amount' => $amount, 'unit' => $channelUnit]
                );
            }
        }
    }
}
