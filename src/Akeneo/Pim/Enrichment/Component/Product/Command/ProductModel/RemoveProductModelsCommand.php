<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Command\ProductModel;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Webmozart\Assert\Assert;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class RemoveProductModelsCommand
{
    /** @var RemoveProductModelCommand[] */
    private array $removeProductModelCommands;

    /** @var ProductModelInterface[]|null */
    private ?array $productModels;

    /**
     * If the product models are already fetched at some point, you can provide them in second argument. It will
     * save some queries and improve performance.
     * Otherwise just provide the RemoveProductModelCommand commands, the handler will also work.
     *
     * @param RemoveProductModelCommand[] $removeProductModelCommands
     * @param ProductModelInterface[]|null $productModels
     */
    private function __construct(array $removeProductModelCommands, array $productModels = null)
    {
        Assert::allIsInstanceOf($removeProductModelCommands, RemoveProductModelCommand::class);
        if (null !== $productModels) {
            Assert::allImplementsInterface($productModels, ProductModelInterface::class);
        }

        $this->removeProductModelCommands = $removeProductModelCommands;
        $this->productModels = $productModels;
    }

    /**
     * Use this factory if you don't have the product models in memory
     *
     * @param RemoveProductModelCommand[] $removeProductModelCommands
     */
    public static function fromRemoveProductModelCommands(array $removeProductModelCommands): RemoveProductModelsCommand
    {
        return new RemoveProductModelsCommand($removeProductModelCommands);
    }

    /**
     * Use this factory if you have the product models in memory
     *
     * @param ProductModelInterface[] $productModels
     */
    public static function fromProductModels(array $productModels): RemoveProductModelsCommand
    {
        Assert::allImplementsInterface($productModels, ProductModelInterface::class);
        $subCommands = \array_map(
            static fn (ProductModelInterface $productModel): RemoveProductModelCommand => new RemoveProductModelCommand(
                $productModel->getCode()
            ),
            $productModels
        );

        return new RemoveProductModelsCommand($subCommands, $productModels);
    }

    /**
     * @return RemoveProductModelCommand[]
     */
    public function removeProductModelCommands(): array
    {
        return $this->removeProductModelCommands;
    }

    /**
     * @return ProductModelInterface[]|null
     */
    public function productModels(): ?array
    {
        return $this->productModels;
    }
}
