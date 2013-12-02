<?php

namespace Oro\Bundle\LocaleBundle\Twig;

use Oro\Bundle\LocaleBundle\Formatter\NameFormatter;
use Oro\Bundle\LocaleBundle\Model\NamePrefixInterface;
use Oro\Bundle\LocaleBundle\Model\FirstNameInterface;
use Oro\Bundle\LocaleBundle\Model\MiddleNameInterface;
use Oro\Bundle\LocaleBundle\Model\LastNameInterface;
use Oro\Bundle\LocaleBundle\Model\NameSuffixInterface;

class NameExtension extends \Twig_Extension
{
    /**
     * @var NameFormatter
     */
    protected $formatter;

    /**
     * @param NameFormatter $formatter
     */
    public function __construct(NameFormatter $formatter)
    {
        $this->formatter = $formatter;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter(
                'oro_format_name',
                array($this, 'format'),
                array('is_safe' => array('html'))
            )
        );
    }

    /**
     * Formats person name according to locale settings.
     *
     * @param NamePrefixInterface|FirstNameInterface|MiddleNameInterface|LastNameInterface|NameSuffixInterface $person
     * @param string $locale
     * @return string
     */
    public function format($person, $locale = null)
    {
        return $this->formatter->format($person, $locale);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_locale_name';
    }
}
