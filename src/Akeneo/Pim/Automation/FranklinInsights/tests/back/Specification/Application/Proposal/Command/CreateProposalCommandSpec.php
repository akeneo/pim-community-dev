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

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Application\Proposal\Command;

use Akeneo\Pim\Automation\FranklinInsights\Application\Proposal\Command\CreateProposalCommand;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\ProductSubscription;
use PhpSpec\ObjectBehavior;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class CreateProposalCommandSpec extends ObjectBehavior
{
    /** @var ProductSubscription */
    private $productSubscription;

    public function let(): void
    {
        $this->productSubscription = new ProductSubscription(42, uniqid(), []);
        $this->beConstructedWith($this->productSubscription);
    }

    public function it_is_a_create_proposal_command(): void
    {
        $this->shouldBeAnInstanceOf(CreateProposalCommand::class);
    }

    public function it_returns_a_product_subscription(): void
    {
        $this->getProductSubscription()->shouldReturn($this->productSubscription);
    }
}
