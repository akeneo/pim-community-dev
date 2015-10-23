<?php

namespace Pim\Bundle\DataGridBundle\Extension\Formatter\Property\ProductValue;

use Pim\Component\Localization\Formatter\FormatterInterface;
use Symfony\Component\Intl\Intl;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Price field property, able to render price attribute type
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PriceProperty extends FieldProperty
{
    /** @var FormatterInterface */
    protected $formatter;

    /**
     * {@inheritdoc}
     *
     * @param TranslatorInterface $translator
     * @param FormatterInterface  $formatter
     */
    public function __construct(TranslatorInterface $translator, FormatterInterface $formatter)
    {
        parent::__construct($translator);

        $this->formatter = $formatter;
    }

    /**
     * {@inheritdoc}
     */
    protected function convertValue($value)
    {
        $data = $this->getBackendData($value);

        $prices = [];
        foreach ($data as $price) {
            if (isset($price['data']) && $price['data'] !== null) {
                $formattedPrice = $this->formatter->format($price['data']);
                $prices[] = sprintf(
                    '%s %s',
                    $formattedPrice,
                    Intl::getCurrencyBundle()->getCurrencySymbol($price['currency'])
                );
            }
        }

        return implode(', ', $prices);
    }
}
