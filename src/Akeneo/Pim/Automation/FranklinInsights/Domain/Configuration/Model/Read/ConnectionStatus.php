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

namespace Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\Model\Read;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
final class ConnectionStatus
{
    /** @var bool */
    private $isActive;

    /** @var bool */
    private $isIdentifiersMappingValid;

    /** @var int */
    private $productSubscriptionCount;

    /**
     * @param bool $isActive
     * @param bool $isIdentifiersMappingValid
     * @param int $productSubscriptionCount
     */
    public function __construct(
        bool $isActive,
        bool $isIdentifiersMappingValid,
        int $productSubscriptionCount
    ) {
        $this->isActive = $isActive;
        $this->isIdentifiersMappingValid = $isIdentifiersMappingValid;
        $this->productSubscriptionCount = $productSubscriptionCount;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->isActive;
    }

    /**
     * @return bool
     */
    public function isIdentifiersMappingValid(): bool
    {
        return $this->isIdentifiersMappingValid;
    }

    /**
     * @return int
     */
    public function productSubscriptionCount()
    {
        return $this->productSubscriptionCount;
    }
}
