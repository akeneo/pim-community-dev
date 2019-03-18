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

namespace Akeneo\Pim\Automation\FranklinInsights\Application\ProductSubscription\Command;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
final class UpdateSubscriptionFamilyCommand
{
    /** @var int */
    private $productId;

    /** @var FamilyCode */
    private $familyCode;

    public function __construct(int $productId, FamilyCode $familyCode)
    {
        $this->productId = $productId;
        $this->familyCode = $familyCode;
    }

    /**
     * @return int
     */
    public function productId(): int
    {
        return $this->productId;
    }

    /**
     * @return FamilyCode
     */
    public function familyCode(): FamilyCode
    {
        return $this->familyCode;
    }
}
