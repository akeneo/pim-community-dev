<?php

namespace Oro\Bundle\DataGridBundle\Extension\Formatter\Property;

use Symfony\Component\Translation\TranslatorInterface;

use Oro\Bundle\DataGridBundle\Extension\Formatter\ResultRecordInterface;

class TranslateableProperty extends FieldProperty
{
    const DOMAIN_KEY = 'domain';
    const LOCALE_KEY = 'locale';

    /** @var array */
    protected $excludeParams = [self::DOMAIN_KEY, self::LOCALE_KEY];

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
    public function getRawValue(ResultRecordInterface $record)
    {
        $value = parent::getRawValue($record);

        return $this->translator->trans(
            $value,
            [],
            $this->getOr(self::DOMAIN_KEY),
            $this->getOr(self::LOCALE_KEY)
        );
    }
}
