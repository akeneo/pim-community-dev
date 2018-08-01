<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Asset\Bundle\Datagrid\Extension\Formatter\Property\Asset;

use Akeneo\Pim\Permission\Bundle\User\UserContext;
use Akeneo\Tool\Component\Localization\Presenter\PresenterInterface;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Property\FieldProperty;

/**
 * Datetime property for an asset.
 *
 * @author Samir Boulil <samir.boulil@akeneo.com>
 */
class DatetimeProperty extends FieldProperty
{
    /** @var UserContext */
    protected $userContext;

    /** @var PresenterInterface */
    protected $presenter;

    /**
     * @param UserContext        $userContext
     * @param PresenterInterface $presenter
     */
    public function __construct(
        UserContext $userContext,
        PresenterInterface $presenter
    ) {
        $this->userContext = $userContext;
        $this->presenter = $presenter;
    }

    /**
     * {@inheritdoc}
     */
    protected function format($datetime)
    {
        return $this->presenter->present($datetime, ['locale' => $this->userContext->getUiLocaleCode()]);
    }
}
