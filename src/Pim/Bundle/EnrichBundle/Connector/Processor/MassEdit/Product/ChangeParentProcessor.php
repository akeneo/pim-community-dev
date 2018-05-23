<?php

declare(strict_types=1);

namespace Pim\Bundle\EnrichBundle\Connector\Processor\MassEdit\Product;

use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Pim\Bundle\EnrichBundle\Connector\Processor\AbstractProcessor;
use Pim\Component\Catalog\Model\EntityWithFamilyVariantInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Processor for mass edit action to change the parent of a given product
 *
 * @author    Julian Prud'homme <julian.prudhomme@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
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

    /**
     * @param $product
     *
     * @return null|EntityWithFamilyVariantInterface
     */
    public function process($product): ?EntityWithFamilyVariantInterface
    {
        $this->validateIsProduct($product);

        $newParentCode = $this->getNewParentCode();
        $this->updateEntity($product, $newParentCode);

        if (! $this->isProductValid($product)) {
            return null;
        }

        return $product;
    }

    /**
     * Validate the given object is the expected product type
     *
     * @param mixed $product
     */
    private function validateIsProduct($product): void
    {
        if (! $product instanceof EntityWithFamilyVariantInterface) {
            throw new \InvalidArgumentException(sprintf('Given entity shoudl be an instance of EntityWithToto. Instance of %s given.', get_class($product)));
        }
    }

    /**
     * Get the new parent code from the input value
     *
     * @return string
     */
    private function getNewParentCode(): string
    {
        $actions = $this->getConfiguredActions();

        return $actions[0]['value'];
    }

    /**
     * Update the product entity with the new parent value
     *
     * @param EntityWithFamilyVariantInterface $product
     * @param string $newParentCode
     *
     * @return void
     */
    private function updateEntity(EntityWithFamilyVariantInterface $product, string $newParentCode): void
    {
        $updater = $this->productUpdater;

        if ($product instanceof ProductModelInterface) {
            $updater = $this->productModelUpdater;
        }

        $updater->update($product, ['parent' => $newParentCode]);
    }

    /**
     * Check if the product to update is valid
     *
     * @param EntityWithFamilyVariantInterface $product
     *
     * @return bool
     */
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
