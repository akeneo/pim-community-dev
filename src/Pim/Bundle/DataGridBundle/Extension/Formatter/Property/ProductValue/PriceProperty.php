<?php

namespace Pim\Bundle\DataGridBundle\Extension\Formatter\Property\ProductValue;

use Akeneo\Component\Localization\Presenter\PresenterInterface;
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
    /** @var PresenterInterface */
    protected $presenter;

    /**
     * @param TranslatorInterface $translator
     * @param PresenterInterface  $presenter
     */
    public function __construct(TranslatorInterface $translator, PresenterInterface $presenter)
    {
        parent::__construct($translator);

        $this->presenter = $presenter;
    }

    /**
     * {@inheritdoc}
     *
     * Presenter works with the standard format which is for a price:
     *   {
     *     "amount": "",
     *     "currency": ""
     *   }
     *
     * Problem is, $value is built by a query (in ProductHydrator) and does not return the standard format.
     * This is why we create a key "amount" to present correctly the data.
     */
    protected function convertValue($value)
    {
        $data = $this->getBackendData($value);

        foreach ($data as $index => $price) {
            if (array_key_exists('data', $price)) {
                $data[$index]['amount'] = $price['data'];
                unset($price['data']);
            } else {
                $data[$index]['amount'] = null;
            }
        }

        return $this->presenter->present($data, ['locale' => $this->translator->getLocale()]);
    }
}
