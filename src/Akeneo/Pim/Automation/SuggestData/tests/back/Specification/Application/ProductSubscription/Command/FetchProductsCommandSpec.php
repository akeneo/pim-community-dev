<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\SuggestData\Component\Command;

use Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Command\FetchProductsCommand;
use PhpSpec\ObjectBehavior;

/**
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class FetchProductsCommandSpec extends ObjectBehavior
{
    public function it_is_a_fetch_products_command()
    {
        $this->shouldHaveType(FetchProductsCommand::class);
    }
}
