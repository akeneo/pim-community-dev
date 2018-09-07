<?php

declare(strict_types=1);

namespace Oro\Bundle\PimDataGridBundle\Extension\Formatter\Property;

use Akeneo\Tool\Component\Localization\Presenter\PresenterInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Property\FieldProperty;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Renders a localized datetime value (similarly to
 * Oro\Bundle\PimDataGridBundle\Extension\Formatter\Property\DateTimeProperty), but apply the current user's timezone
 * on it.
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DateTimeWithUserTimezoneProperty extends FieldProperty
{
    /** @var PresenterInterface */
    private $presenter;

    /** @var UserContext */
    private $userContext;

    /**
     * @param TranslatorInterface $translator
     * @param PresenterInterface  $presenter
     * @param UserContext         $userContext
     */
    public function __construct(
        TranslatorInterface $translator,
        PresenterInterface $presenter,
        UserContext $userContext
    ) {
        parent::__construct($translator);

        $this->presenter = $presenter;
        $this->userContext = $userContext;
    }

    /**
     * {@inheritdoc}
     */
    protected function convertValue($value)
    {
        return $this->presenter->present(
            $value,
            [
                'locale'   => $this->userContext->getUiLocaleCode(),
                'timezone' => $this->userContext->getUserTimezone(),
            ]
        );
    }
}
