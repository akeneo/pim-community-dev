<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\MassEdit;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyVariantInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Tool\Component\Batch\Item\DataInvalidItem;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Exception\PropertyException;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\Common\Util\ClassUtils;
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
     * @param mixed $product
     *
     * @return null|EntityWithFamilyVariantInterface
     */
    public function process($product): ?EntityWithFamilyVariantInterface
    {
        $this->validateIsProduct($product);

        $newParentCode = $this->getNewParentCode();

        try {
            $this->updateEntity($product, $newParentCode);

            if (! $this->isProductValid($product)) {
                return null;
            }
        } catch (PropertyException $e) {
            $this->stepExecution->addWarning($e->getMessage(), [], new DataInvalidItem($product));
            return null;
        }

        return $product;
    }

    /**
     * Validate the given object is the expected product type
     *
     * @param mixed $product
     *
     * @throws InvalidObjectException
     */
    private function validateIsProduct($product): void
    {
        if (! $product instanceof EntityWithFamilyVariantInterface) {
            throw InvalidObjectException::objectExpected(ClassUtils::getClass($product), EntityWithFamilyVariantInterface::class);
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
