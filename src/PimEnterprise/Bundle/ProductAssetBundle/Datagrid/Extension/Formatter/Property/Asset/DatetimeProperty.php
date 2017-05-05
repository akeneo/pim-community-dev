<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductAssetBundle\Datagrid\Extension\Formatter\Property\Asset;

use Akeneo\Component\Localization\Presenter\PresenterInterface;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Property\FieldProperty;
use PimEnterprise\Bundle\UserBundle\Context\UserContext;

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

    /**
     * {@inheritdoc}
     */
    protected function getRawValue(ResultRecordInterface $record)
    {
        try {
            $value = $record->getValue($this->getOr(self::DATA_NAME_KEY, $this->get(self::NAME_KEY)));
        } catch (\LogicException $e) {
            return null;
        }

        return $this->format($value);
    }
}
