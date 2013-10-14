<?php

namespace Oro\Bundle\DataGridBundle\Extension\Formatter\Property;

use Symfony\Component\Translation\TranslatorInterface;

use Oro\Bundle\DataGridBundle\Extension\Formatter\ResultRecordInterface;

class TranslateableProperty extends AbstractProperty
{
    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritDoc}
     */
    public function getValue(ResultRecordInterface $record)
    {
        $dataField = $this->getOr('alias', $this->get('name'));
        $value     = $record->getValue($dataField);

        return $this->translator->trans($value, array(), $this->getOr('domain'), $this->getOr('locale'));
    }
}
