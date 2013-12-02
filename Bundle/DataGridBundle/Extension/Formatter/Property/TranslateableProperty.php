<?php

namespace Oro\Bundle\DataGridBundle\Extension\Formatter\Property;

use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;

class TranslateableProperty extends FieldProperty
{
    const DOMAIN_KEY = 'domain';
    const LOCALE_KEY = 'locale';

    /** @var array */
    protected $excludeParams = [self::DOMAIN_KEY, self::LOCALE_KEY];

    /**
     * {@inheritDoc}
     */
    public function getRawValue(ResultRecordInterface $record)
    {
        $value = parent::getRawValue($record);

        return $this->translator->trans($value, [], $this->getOr(self::DOMAIN_KEY), $this->getOr(self::LOCALE_KEY));
    }
}
