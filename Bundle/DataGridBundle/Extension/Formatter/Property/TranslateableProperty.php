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
     * @var null|string
     */
    protected $domain = null;

    /**
     * @var null|string
     */
    protected $locale = null;

    /**
     * @var null|string
     */
    protected $alias = null;

    /**
     * @param string $name
     * @param TranslatorInterface $translator
     * @param string $alias
     * @param string $domain
     * @param string $locale
     */
    public function __construct($name, TranslatorInterface $translator, $alias = null, $domain = null, $locale = null)
    {
        $this->name = $name;
        $this->translator = $translator;
        $this->alias = $alias;
        $this->domain = $domain;
        $this->locale = $locale;
    }

    /**
     * {@inheritDoc}
     */
    public function getValue(ResultRecordInterface $record)
    {
        $dataField = $this->alias ? : $this->getName();
        $value = $record->getValue($dataField);

        return $this->translator->trans($value, array(), $this->domain, $this->locale);
    }
}
