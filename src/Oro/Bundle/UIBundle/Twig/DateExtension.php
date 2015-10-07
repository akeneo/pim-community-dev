<?php

namespace Oro\Bundle\UIBundle\Twig;

use Symfony\Component\Translation\TranslatorInterface;

class DateExtension extends \Twig_Extension
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
     * @return array
     */
    public function getFilters()
    {
        return array(
            'age' => new \Twig_Filter_Method($this, 'getAge'),
            'age_string' => new \Twig_Filter_Method($this, 'getAgeAsString'),
        );
    }

    /**
     * Get age as number of years.
     *
     * @param string|\DateTime $date
     * @param array $options
     * @return int
     */
    public function getAge($date, $options)
    {
        $dateDiff = $this->getDateDiff($date, $options);
        if ($dateDiff->invert) {
            return null;
        } else {
            return $dateDiff->y;
        }
    }

    /**
     * Get translated age string.
     *
     * @param string|\DateTime $date
     * @param array $options
     * @return string
     */
    public function getAgeAsString($date, $options)
    {
        $dateDiff = $this->getDateDiff($date, $options);
        if (!$dateDiff->invert) {
            $age = $dateDiff->y;
            return $this->translator->transChoice('oro.age', $age, array('%count%' => $age), 'messages');
        } else {
            return isset($options['default']) ? $options['default'] : '';
        }
    }

    protected function getDateDiff($date, $options)
    {
        if (!$date instanceof \DateTime) {
            $format = isset($options['format']) ? $options['format'] : 'Y-m-d';
            $tz = (isset($options['timezone'])) ? new \DateTimeZone($options['timezone']) : new \DateTimeZone('UTC');
            $date = \DateTime::createFromFormat($format, $date, $tz);
        }
        return $date->diff(new \DateTime('now'));
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'oro_ui.date';
    }
}
