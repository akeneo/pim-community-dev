<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Command\ProductModel;

use Akeneo\Pim\Enrichment\Component\Product\Command\ProductModel\RemoveProductModelCommand;
use Akeneo\Pim\Enrichment\Component\Product\Command\ProductModel\RemoveProductModelsCommand;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use PhpSpec\ObjectBehavior;

final class RemoveProductModelsCommandSpec extends ObjectBehavior
{
    function it_can_be_constructed_with_product_models()
    {
        $pm1 = new ProductModel();
        $pm1->setCode('pm1');
        $pm2 = new ProductModel();
        $pm2->setCode('pm2');

        $this->beConstructedThrough('fromProductModels', [[$pm1, $pm2]]);
        $this->shouldHaveType(RemoveProductModelsCommand::class);

        $this->productModels()->shouldReturn([$pm1, $pm2]);
        $this->removeProductModelCommands()->shouldBeLike([
            new RemoveProductModelCommand('pm1'),
            new RemoveProductModelCommand('pm2'),
        ]);
    }

    function it_can_be_constructed_with_commands()
    {
        $command1 = new RemoveProductModelCommand('pm1');
        $command2 = new RemoveProductModelCommand('pm2');
        $this->beConstructedThrough('fromRemoveProductModelCommands', [[$command1, $command2]]);
        $this->shouldHaveType(RemoveProductModelsCommand::class);

        $this->productModels()->shouldReturn(null);
        $this->removeProductModelCommands()->shouldReturn([$command1, $command2]);
    }

    function it_can_be_constructed_only_with_product_models()
    {
        $pm1 = new ProductModel();
        $pm1->setCode('pm1');
        $other = new \stdClass();

        $this->beConstructedThrough('fromProductModels', [[$pm1, $other]]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_can_be_constructed_only_with_remove_product_model_command()
    {
        $command1 = new RemoveProductModelCommand('pm1');
        $other = new \stdClass();

        $this->beConstructedThrough('fromRemoveProductModelCommands', [[$command1, $other]]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }
}
