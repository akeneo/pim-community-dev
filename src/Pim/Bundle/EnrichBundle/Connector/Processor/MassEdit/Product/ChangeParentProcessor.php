<?php

declare(strict_types=1);

namespace Pim\Bundle\EnrichBundle\Connector\Processor\MassEdit\Product;

use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Pim\Bundle\EnrichBundle\Connector\Processor\AbstractProcessor;
use Pim\Component\Catalog\Model\EntityWithFamilyVariantInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ChangeParentProcessor extends AbstractProcessor
{
    /** @var ValidatorInterface */
    protected $productValidator;

    /** @var ValidatorInterface */
    protected $productModelValidator;

    /** @var ObjectUpdaterInterface */
    protected $productUpdater;

    /** @var ObjectUpdaterInterface */
    protected $productModelUpdater;

    public function __construct(
        ValidatorInterface $productValidator,
        ValidatorInterface $productModelValidator,
        ObjectUpdaterInterface $productUpdater,
        ObjectUpdaterInterface $productModelUpdater
    ) {
        $this->productValidator = $productValidator;
        $this->productModelValidator = $productModelValidator;
        $this->productUpdater = $productUpdater;
        $this->productModelUpdater = $productModelUpdater;
    }

    public function process($product)
    {
        $this->validateProduct($product);

        $newParentCode = $this->getNewParentCode();
        $this->updateEntity($product, $newParentCode);

        if (! $this->isProductValid($product)) {
            return null;
        }

        return $product;
    }

    private function validateProduct($product)
    {
        if (! $product instanceof EntityWithFamilyVariantInterface) {
            throw new \InvalidArgumentException('The product is not correct');
        }
    }

    private function getNewParentCode()
    {
        $actions = $this->getConfiguredActions();

        return $actions[0]['value'];
    }

    private function updateEntity(EntityWithFamilyVariantInterface $product, string $newParentCode): void
    {
        $updater = $this->productUpdater;

        if ($product instanceof ProductModelInterface) {
            $updater = $this->productModelUpdater;
        }

        $updater->update($product, ['parent' => $newParentCode]);
    }

    private function isProductValid(EntityWithFamilyVariantInterface $product): bool
    {
        $validator = $this->productValidator;

        if ($product instanceof ProductModelInterface) {
            $validator = $this->productModelValidator;
        }

        $violations = $validator->validate($product);
        $this->addWarningMessage($violations, $product);

        return 0 === $violations->count();
    }
}
