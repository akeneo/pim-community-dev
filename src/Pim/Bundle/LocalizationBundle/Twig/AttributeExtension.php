<?php

namespace Pim\Bundle\LocalizationBundle\Twig;

use Pim\Component\Localization\LocaleResolver;
use Pim\Component\Localization\Presenter\PresenterInterface;

/**
 * Twig extension to present localized data
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @TODO: find an other name
 */
class AttributeExtension extends \Twig_Extension
{
    /** @var PresenterInterface */
    protected $datePresenter;

    /** @var LocaleResolver */
    protected $localeResolver;

    /**
     * @param PresenterInterface $datePresenter
     * @param LocaleResolver       $localeResolver
     */
    public function __construct(/*PresenterInterface $datePresenter, */LocaleResolver $localeResolver)
    {
//        $this->datePresenter  = $datePresenter;
        $this->localeResolver = $localeResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_localization.twig.attribute_extension';
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('datetime_presenter', [$this, 'datetimePresenter'], ['is_safe' => ['html']]),
            new \Twig_SimpleFilter('date_presenter', [$this, 'datePresenter'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * Present a datetime
     *
     * @param string $date
     *
     * @return string
     */
    public function datetimePresenter($date)
    {
        return 'YOLO'; // @todo wait PIM-5033
        return $this->datePresenter->present($date, ['locale' => $this->localeResolver->getCurrentLocale()]);
    }

    /**
     * Present a date
     *
     * @param string $date
     *
     * @return string
     */
    public function datePresenter($date)
    {
        return 'YOLO'; // @todo wait PIM-5033
        return $this->datePresenter->present($date, ['locale' => $this->localeResolver->getCurrentLocale()]);
    }
}
