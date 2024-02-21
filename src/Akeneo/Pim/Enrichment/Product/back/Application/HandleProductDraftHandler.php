<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Application;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\Exception\ViolationsException;
use Akeneo\Pim\Enrichment\Product\API\Command\HandleProductDraftCommand;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Webmozart\Assert\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class HandleProductDraftHandler
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
        private readonly ValidatorInterface $validator,
        private readonly ObjectUpdaterInterface $productUpdater,
        private readonly SaverInterface $productSaver,
    ) {
    }

    /**
     * @throws ViolationsException
     */
    public function __invoke(HandleProductDraftCommand $command): void
    {
        $product = $this->productRepository->find($command->getUuid());
        Assert::isInstanceOf($product, ProductInterface::class);
        $this->productUpdater->update($product, $command->getData());

        $violations = $this->validator->validate($product);
        if (0 !== $violations->count()) {
            throw new ViolationsException($violations);
        }

        $this->productSaver->save($product);
    }
}
