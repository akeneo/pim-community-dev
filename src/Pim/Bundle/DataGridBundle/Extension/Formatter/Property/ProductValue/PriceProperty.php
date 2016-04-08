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
     */
    protected function convertValue($value)
    {
        $data = $this->getBackendData($value);

        return $this->presenter->present($data, ['locale' => $this->translator->getLocale()]);
    }
}
