<?php

namespace Oro\Bundle\DataGridBundle\Extension\Formatter\Property;

use Symfony\Component\Translation\TranslatorInterface;

use Oro\Bundle\DataGridBundle\Extension\Formatter\ResultRecordInterface;

class TranslateableProperty extends AbstractProperty
{
    const ALIAS_KEY  = 'alias';
    const DOMAIN_KEY = 'domain';
    const LOCALE_KEY = 'locale';

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
        $dataField = $this->getOr(self::ALIAS_KEY, $this->get(self::NAME_KEY));
        $value     = $record->getValue($dataField);

        return $this->translator->trans(
            $value,
            [],
            $this->getOr(self::DOMAIN_KEY),
            $this->getOr(self::LOCALE_KEY)
        );
    }
}
