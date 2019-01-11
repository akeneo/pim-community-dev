<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\Read;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\Model\Read\ConnectionStatus;

/**
 * Read model containing the status of a product subscription to Franklin.
 *
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
final class ProductSubscriptionStatus
{
    /** @var bool */
    private $isSubscribed;

    /** @var ConnectionStatus */
    private $connectionStatus;

    /** @var bool */
    private $hasFamily;

    /** @var bool */
    private $isMappingFilled;

    /** @var bool */
    private $isProductVariant;

    /**
     * @param ConnectionStatus $connectionStatus
     * @param bool $isSubscribed
     * @param bool $hasFamily
     * @param bool $isMappingFilled
     * @param bool $isProductVariant
     */
    public function __construct(
        ConnectionStatus $connectionStatus,
        bool $isSubscribed,
        bool $hasFamily,
        bool $isMappingFilled,
        bool $isProductVariant
    ) {
        $this->isSubscribed = $isSubscribed;
        $this->connectionStatus = $connectionStatus;
        $this->hasFamily = $hasFamily;
        $this->isMappingFilled = $isMappingFilled;
        $this->isProductVariant = $isProductVariant;
    }

    /**
     * @return bool
     */
    public function isSubscribed(): bool
    {
        return $this->isSubscribed;
    }

    /**
     * @return ConnectionStatus
     */
    public function getConnectionStatus(): ConnectionStatus
    {
        return $this->connectionStatus;
    }

    /**
     * @return bool
     */
    public function hasFamily(): bool
    {
        return $this->hasFamily;
    }

    /**
     * @return bool
     */
    public function isMappingFilled(): bool
    {
        return $this->isMappingFilled;
    }

    /**
     * @return bool
     */
    public function isProductVariant(): bool
    {
        return $this->isProductVariant;
    }
}
