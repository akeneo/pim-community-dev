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

use Akeneo\Pim\Structure\Component\Model\FamilyInterface;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
final class UpdateSubscriptionFamilyCommand
{
    /** @var int */
    private $productId;

    /** @var FamilyInterface */
    private $family;

    /**
     * @param int $productId
     * @param FamilyInterface $family
     */
    public function __construct(int $productId, FamilyInterface $family)
    {
        $this->productId = $productId;
        // TODO APAI-476: remove the $family argument
        $this->family = $family;
    }

    /**
     * @return int
     */
    public function productId(): int
    {
        return $this->productId;
    }

    /**
     * @return FamilyInterface
     */
    public function family(): Familyinterface
    {
        return $this->family;
    }
}
