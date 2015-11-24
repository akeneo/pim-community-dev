<?php

namespace Pim\Bundle\LocalizationBundle\Controller;

use Pim\Component\Localization\Factory\DateFactory;
use Pim\Component\Localization\LocaleResolver;
use Pim\Component\Localization\Localizer\LocalizerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Format controller
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FormatController
{
    /** @var DateFactory */
    protected $factory;

    /** @var LocaleResolver */
    protected $localeResolver;

    /**
     * @param DateFactory    $factory
     * @param LocaleResolver $localeResolver
     */
    public function __construct(DateFactory $factory, LocaleResolver $localeResolver)
    {
        $this->factory        = $factory;
        $this->localeResolver = $localeResolver;
    }

    /**
     * Date format action (show pattern expected for current locale)
     *
     * @return JsonResponse
     */
    public function dateAction()
    {
        $locale    = $this->localeResolver->getCurrentLocale();
        $formatter = $this->factory->create(['locale' => $locale]);

        return new JsonResponse(
            [
                'format'        => $formatter->getPattern(),
                'defaultFormat' => LocalizerInterface::DEFAULT_DATE_FORMAT,
                'language'      => $locale,
            ]
        );
    }
}
