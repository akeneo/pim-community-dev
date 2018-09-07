<?php

namespace Oro\Bundle\PimDataGridBundle\Extension\Formatter\Property;

use Akeneo\Tool\Component\Localization\Presenter\PresenterInterface;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;
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

    /**
     * {@inheritdoc}
     */
    protected function getRawValue(ResultRecordInterface $record)
    {
        try {
            $value = $record->getValue($this->getOr(self::DATA_NAME_KEY, $this->get(self::NAME_KEY)));
        } catch (\LogicException $e) {
            // default value
            $value = null;
        }

        return $value;
    }
}
