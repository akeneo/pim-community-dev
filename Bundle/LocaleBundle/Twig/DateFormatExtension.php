<?php

namespace Oro\Bundle\LocaleBundle\Twig;

class DateFormatExtension extends \Twig_Extension
{
    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return array(
            'convert_format' => new \Twig_SimpleFilter('convert_format', array($this, 'convertDateFormat'), array('needs_environment' => true))
            //'localizeddate' => new Twig_Filter_Function('twig_localized_date_filter', array('needs_environment' => true)),
        );
    }

    /**
     *
     * @param \DateTime $date
     * @return string
     */
    public function formatDateTime(Twig_Environment $env, $dateTimeFormat, $)
    {
        twig_escape_filter(
            $this->env,
            twig_localized_date_filter(
                $this->env,
                (isset($context["time"]) ? $context["time"] : $this->getContext($context, "time")),
                "short",
                "short"
            ),
            "html",
            null,
            true
        );

        return ;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_locale';
    }
}
