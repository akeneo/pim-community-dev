<?php

namespace Oro\Bundle\LocaleBundle\Model;

use Symfony\Component\DependencyInjection\ContainerInterface;

class CalendarFactory implements CalendarFactoryInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function getCalendar($locale = null, $language = null)
    {
        /** @var Calendar $result */
        $result = $this->container->get('oro_locale.calendar');
        $result->setLocale($locale);
        $result->setLanguage($language);
        return $result;
    }
}
