<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\MassEdit;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Tool\Component\Batch\Item\DataInvalidItem;
use Akeneo\Tool\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Processor to add product value in a mass edit
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @deprecated will be removed in 2.1, please use instead
 *             Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\MassEdit\EditAttributesProcessor
 */
class EditCommonAttributesProcessor extends AbstractProcessor
{
    /** @var ValidatorInterface */
    protected $validator;

    /** @var ProductRepositoryInterface */
    protected $productRepository;

    /** @var array */
    protected $skippedAttributes = [];

    /** @var ObjectUpdaterInterface */
    protected $productUpdater;

    /** @var ObjectDetacherInterface */
    protected $productDetacher;

    /**
     * @param ValidatorInterface                  $validator
     * @param ProductRepositoryInterface          $productRepository
     * @param ObjectUpdaterInterface              $productUpdater
     * @param ObjectDetacherInterface             $productDetacher
     */
    public function __construct(
        ValidatorInterface $validator,
        ProductRepositoryInterface $productRepository,
        ObjectUpdaterInterface $productUpdater,
        ObjectDetacherInterface $productDetacher
    ) {
        $this->validator = $validator;
        $this->productRepository = $productRepository;
        $this->productUpdater = $productUpdater;
        $this->productDetacher = $productDetacher;
    }

    /**
     * {@inheritdoc}
     */
    public function process($product)
    {
        $actions = $this->getConfiguredActions();

        if (!$this->isProductEditable($product)) {
            $this->stepExecution->incrementSummaryInfo('skipped_products');
            $this->productDetacher->detach($product);

            return null;
        }

        $product = $this->updateProduct($product, $actions[0]);
        if (null !== $product && !$this->isProductValid($product)) {
            $this->stepExecution->incrementSummaryInfo('skipped_products');
            $this->productDetacher->detach($product);

            return null;
        }

        return $product;
    }

    /**
     * Set data from $actions to the given $product
     *
     * Actions should look like that
     *
     * $actions =
     * [
     *      'normalized_values' => [
     *          'name' => [
     *              [
     *                  'locale' => null,
     *                  'scope'  => null,
     *                  'data' => 'The name'
     *              ]
     *          ],
     *          'description' => [
     *              [
     *                  'locale' => 'en_US',
     *                  'scope' => 'ecommerce',
     *                  'data' => 'The description for en_US ecommerce'
     *              ]
     *          ]
     *      ]
     * ]
     *
     * @param ProductInterface $product
     * @param array            $actions
     *
     * @throws \LogicException
     *
     * @return ProductInterface $product
     */
    protected function updateProduct(ProductInterface $product, array $actions)
    {
        $normalizedValues = $actions['normalized_values'];
        $filteredValues = [];

        foreach ($normalizedValues as $attributeCode => $values) {
            /**
             * We don't call that method directly on the product model because it hydrates
             * lot of models and it causes memory leak...
             */
            if ($this->isAttributeEditable($product, $attributeCode)) {
                $filteredValues['values'][$attributeCode] = $values;
            }
        }

        if (empty($filteredValues)) {
            $this->stepExecution->incrementSummaryInfo('skipped_products');
            $this->addWarning($product);
            $this->productDetacher->detach($product);

            return null;
        }

        $this->productUpdater->update($product, $filteredValues);

        return $product;
    }

    protected function isAttributeEditable(ProductInterface $product, string $attributeCode): bool
    {
        if (!$this->productRepository->hasAttributeInFamily($product->getId(), $attributeCode)) {
            return false;
        }

        return true;
    }

    /**
     * Validate the product
     *
     * @param ProductInterface $product
     *
     * @return bool
     */
    protected function isProductValid(ProductInterface $product)
    {
        $violations = $this->validator->validate($product);
        $this->addWarningMessage($violations, $product);

        return 0 === $violations->count();
    }

    /**
     * @param ProductInterface $product
     *
     * @return bool
     */
    protected function isProductEditable(ProductInterface $product)
    {
        return true;
    }

    /**
     * @param ProductInterface $product
     */
    protected function addWarning(ProductInterface $product)
    {
        /*
         * We don't give the product to addWarning because we don't want that step executor
         * calls the toString method which hydrate lot of model
         */
        $this->stepExecution->addWarning(
            'pim_enrich.mass_edit_action.edit-common-attributes.message.no_valid_attribute',
            [],
            new DataInvalidItem(
                [
                    'class'  => ClassUtils::getClass($product),
                    'id'     => $product->getId(),
                    'string' => $product->getIdentifier(),
                ]
            )
        );
    }
}
