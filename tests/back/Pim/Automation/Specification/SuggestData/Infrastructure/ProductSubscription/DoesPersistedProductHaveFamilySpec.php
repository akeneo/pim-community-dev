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

namespace Specification\Akeneo\Pim\Automation\SuggestData\Infrastructure\ProductSubscription;

use Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Service\DoesPersistedProductHaveFamilyInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\ProductSubscription\DoesPersistedProductHaveFamily;
use Doctrine\ORM\EntityManagerInterface;
use PhpSpec\ObjectBehavior;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class DoesPersistedProductHaveFamilySpec extends ObjectBehavior
{
    public function let(EntityManagerInterface $entityManager): void
    {
        $this->beConstructedWith($entityManager);
    }

    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(DoesPersistedProductHaveFamily::class);
    }

    public function it_is_a_service_that_checks_if_a_persisted_product_has_family(): void
    {
        $this->shouldImplement(DoesPersistedProductHaveFamilyInterface::class);
    }
}
