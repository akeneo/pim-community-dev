<?php

namespace Pim\Component\Catalog\Converter;

use Akeneo\Tool\Bundle\MeasureBundle\Convert\MeasureConverter;
use Pim\Component\Catalog\Builder\EntityWithValuesBuilderInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\EntityWithValuesInterface;
use Pim\Component\Catalog\Model\MetricInterface;

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

    /** @var EntityWithValuesBuilderInterface */
    protected $entityWithValuesBuilder;

    /**
     * @param MeasureConverter                 $converter
     * @param EntityWithValuesBuilderInterface $entityWithValuesBuilder
     */
    public function __construct(MeasureConverter $converter, EntityWithValuesBuilderInterface $entityWithValuesBuilder)
    {
        $this->converter               = $converter;
        $this->entityWithValuesBuilder = $entityWithValuesBuilder;
    }

    /**
     * Convert all the metric values into the channel configured conversion units
     *
     * @param EntityWithValuesInterface $entityWithValues
     * @param ChannelInterface          $channel
     */
    public function convert(EntityWithValuesInterface $entityWithValues, ChannelInterface $channel)
    {
        $channelUnits = $channel->getConversionUnits();
        foreach ($entityWithValues->getValues() as $value) {
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

                $this->entityWithValuesBuilder->addOrReplaceValue(
                    $entityWithValues,
                    $attribute,
                    $value->getLocale(),
                    $value->getScope(),
                    ['amount' => $amount, 'unit' => $channelUnit]
                );
            }
        }
    }
}
