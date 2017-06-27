<?php

namespace Pim\Bundle\DataGridBundle\Extension\Formatter\Property;

use Akeneo\Component\Localization\Presenter\PresenterInterface;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Property\FieldProperty;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Able to render datetime value
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DateTimeProperty extends FieldProperty
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
        $result = !$value instanceof \DateTime ? $this->getBackendData($value) : $value;

        return $this->presenter->present($result, ['locale' => $this->translator->getLocale()]);
    }
}
