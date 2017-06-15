<?php

namespace Pim\Component\Catalog\Converter;

use Akeneo\Bundle\MeasureBundle\Convert\MeasureConverter;
use Pim\Component\Catalog\Builder\ValuesContainerBuilderInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\MetricInterface;
use Pim\Component\Catalog\Model\ValuesContainerInterface;

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

    /** @var ValuesContainerBuilderInterface */
    protected $valuesContainerBuilder;

    /**
     * @param MeasureConverter                $converter
     * @param ValuesContainerBuilderInterface $valuesContainerBuilder
     */
    public function __construct(MeasureConverter $converter, ValuesContainerBuilderInterface $valuesContainerBuilder)
    {
        $this->converter = $converter;
        $this->valuesContainerBuilder = $valuesContainerBuilder;
    }

    /**
     * Convert all the metric values into the channel configured conversion units
     *
     * @param ValuesContainerInterface $valuesContainer
     * @param ChannelInterface         $channel
     */
    public function convert(ValuesContainerInterface $valuesContainer, ChannelInterface $channel)
    {
        $channelUnits = $channel->getConversionUnits();
        foreach ($valuesContainer->getValues() as $value) {
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

                $this->valuesContainerBuilder->addOrReplaceValue(
                    $valuesContainer,
                    $attribute,
                    $value->getLocale(),
                    $value->getScope(),
                    ['amount' => $amount, 'unit' => $channelUnit]
                );
            }
        }
    }
}
