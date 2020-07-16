<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\StandardToFlat\Product\ValueConverter;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\AttributeColumnsResolver;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Metric array converter.
 * Convert a standard metric array format to a flat one.
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class MetricConverter extends AbstractValueConverter implements ValueConverterInterface
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(AttributeColumnsResolver $columnsResolver, TranslatorInterface $translator, array $supportedAttributeTypes)
    {
        parent::__construct($columnsResolver, $supportedAttributeTypes);
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     *
     * Convert a standard formatted metric product value to a flat one.
     *
     * Given a 'weight' $attributeCode with this $data:
     * [
     *     [
     *         'locale' => 'de_DE',
     *         'scope'  => 'print',
     *         'data'   => [
     *             'unit'   => 'MEGAHERTZ',
     *             'amount' => '100'
     *         ]
     *     ]
     * ]
     *
     * It will return:
     * [
     *     'weight-de_DE-print'      => '100',
     *     'weight-de_DE-print-unit' => 'MEGAHERTZ',
     * ]
     */
    public function convert($attributeCode, $data)
    {
        $convertedItem = [];
        $unitLabel = $this->translator->trans('pim_common.unit', [], null, 'fr_FR');

        foreach ($data as $value) {
            $flatName = $this->columnsResolver->resolveFlatAttributeName(
                $attributeCode,
                $value['locale'],
                $value['scope']
            );
            $flatUnitName = sprintf('%s (%s)', $flatName, $unitLabel);

            if (null === $value['data']['amount']) {
                $convertedItem[$flatName] = null;
                $convertedItem[$flatUnitName] = null;

                continue;
            }

            $convertedItem[$flatName] = (string) $value['data']['amount'];
            $convertedItem[$flatUnitName] = $value['data']['unit'];
        }

        return $convertedItem;
    }
}
