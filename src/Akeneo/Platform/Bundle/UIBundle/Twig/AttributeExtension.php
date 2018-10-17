<?php

namespace Akeneo\Platform\Bundle\UIBundle\Twig;

use Akeneo\Platform\Bundle\UIBundle\Resolver\LocaleResolver;
use Akeneo\Tool\Component\Localization\Presenter\PresenterInterface;

/**
 * Twig extension to present localized data
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeExtension extends \Twig_Extension
{
    /** @var PresenterInterface */
    protected $datePresenter;

    /** @var PresenterInterface */
    protected $datetimePresenter;

    /** @var LocaleResolver */
    protected $localeResolver;

    /**
     * @param PresenterInterface $datePresenter
     * @param PresenterInterface $datetimePresenter
     * @param LocaleResolver     $localeResolver
     */
    public function __construct(
        PresenterInterface $datePresenter,
        PresenterInterface $datetimePresenter,
        LocaleResolver $localeResolver
    ) {
        $this->datePresenter = $datePresenter;
        $this->datetimePresenter = $datetimePresenter;
        $this->localeResolver = $localeResolver;
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
        return $this->datetimePresenter->present($date, ['locale' => $this->localeResolver->getCurrentLocale()]);
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
        return $this->datePresenter->present($date, ['locale' => $this->localeResolver->getCurrentLocale()]);
    }
}
