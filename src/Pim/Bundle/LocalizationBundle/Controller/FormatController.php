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
    protected $dateFactory;

    /** @var DateFactory */
    protected $datetimeFactoty;

    /** @var LocaleResolver */
    protected $localeResolver;

    /**
     * @param DateFactory    $dateFactory
     * @param DateFactory    $datetimeFactory
     * @param LocaleResolver $localeResolver
     */
    public function __construct(DateFactory $dateFactory, DateFactory $datetimeFactory, LocaleResolver $localeResolver)
    {
        $this->dateFactory     = $dateFactory;
        $this->datetimeFactory = $datetimeFactory;
        $this->localeResolver  = $localeResolver;
    }

    /**
     * Date format action (show pattern expected for current locale)
     *
     * @return JsonResponse
     */
    public function dateAction()
    {
        $locale    = $this->localeResolver->getCurrentLocale();
        $dateFormatter = $this->dateFactory->create(['locale' => $locale]);
        $timeFormatter = $this->datetimeFactory->create(['locale' => $locale]);

        return new JsonResponse(
            [
                'date'     => [
                    'format'        => $dateFormatter->getPattern(),
                    'defaultFormat' => LocalizerInterface::DEFAULT_DATE_FORMAT,
                ],
                'time'     => [
                    'format'        => $timeFormatter->getPattern(),
                    'defaultFormat' => LocalizerInterface::DEFAULT_DATETIME_FORMAT,
                ],
                'language' => $locale,
                '12Hours'  => 0 !== strpos($timeFormatter->getPattern(), 'a')
            ]
        );
    }
}
