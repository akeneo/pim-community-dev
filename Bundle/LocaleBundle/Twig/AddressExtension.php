<?php

namespace Oro\Bundle\LocaleBundle\Twig;

use Oro\Bundle\LocaleBundle\Model\AddressInterface;
use Oro\Bundle\LocaleBundle\Formatter\AddressFormatter;

class AddressExtension extends \Twig_Extension
{
    /**
     * @var AddressFormatter
     */
    protected $formatter;

    /**
     * @param AddressFormatter $formatter
     */
    public function __construct(AddressFormatter $formatter)
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
                'oro_format_address',
                array($this, 'format'),
                array('is_safe' => array('html'))
            )
        );
    }

    /**
     * Formats address according to locale settings.
     *
     * @param AddressInterface $address
     * @param string|null $country
     * @param string $newLineSeparator
     * @return string
     */
    public function format(AddressInterface $address, $country = null, $newLineSeparator = "<br/>")
    {
        return $this->formatter->format($address, $country, $newLineSeparator);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_locale_address';
    }
}
