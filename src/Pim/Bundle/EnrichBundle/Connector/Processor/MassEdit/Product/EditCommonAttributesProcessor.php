<?php

namespace Pim\Bundle\EnrichBundle\Connector\Processor\MassEdit\Product;

use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\Component\StorageUtils\Updater\PropertySetterInterface;
use Doctrine\Common\Util\ClassUtils;
use Pim\Bundle\CatalogBundle\Exception\InvalidArgumentException;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface;
use Pim\Bundle\EnrichBundle\Connector\Processor\AbstractProcessor;
use Pim\Component\Connector\Repository\JobConfigurationRepositoryInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Processor to add product value in a mass edit
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EditCommonAttributesProcessor extends AbstractProcessor
{
    /** @var ValidatorInterface */
    protected $validator;

    /** @var ProductRepositoryInterface */
    protected $productRepository;

    /** @var PropertySetterInterface */
    protected $propertySetter;

    /** @var array */
    protected $skippedAttributes = [];

    /** @var ObjectUpdaterInterface */
    protected $productUpdater;

    /** @var ObjectDetacherInterface */
    protected $productDetacher;

    /**
     * @param PropertySetterInterface             $propertySetter
     * @param ValidatorInterface                  $validator
     * @param ProductRepositoryInterface          $productRepository
     * @param JobConfigurationRepositoryInterface $jobConfigurationRepo
     * @param ObjectUpdaterInterface              $productUpdater
     * @param ObjectDetacherInterface             $productDetacher
     */
    public function __construct(
        PropertySetterInterface $propertySetter,
        ValidatorInterface $validator,
        ProductRepositoryInterface $productRepository,
        JobConfigurationRepositoryInterface $jobConfigurationRepo,
        ObjectUpdaterInterface $productUpdater,
        ObjectDetacherInterface $productDetacher
    ) {
        parent::__construct($jobConfigurationRepo);

        $this->propertySetter    = $propertySetter;
        $this->validator         = $validator;
        $this->productRepository = $productRepository;
        $this->productUpdater    = $productUpdater;
        $this->productDetacher   = $productDetacher;
    }

    /**
     * {@inheritdoc}
     */
    public function process($product)
    {
        $configuration = $this->getJobConfiguration();

        if (!array_key_exists('actions', $configuration)) {
            throw new InvalidArgumentException('Missing configuration for \'actions\'.');
        }

        if (!$this->isProductEditable($product)) {
            $this->stepExecution->incrementSummaryInfo('skipped_products');
            $this->productDetacher->detach($product);

            return null;
        }

        $actions = $configuration['actions'];
        $product = $this->updateProduct($product, $actions);
        if (null !== $product) {
            if (!$this->isProductValid($product)) {
                $this->stepExecution->incrementSummaryInfo('skipped_products');
                $this->productDetacher->detach($product);

                return null;
            }
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
     *                  'data' => 'The description for ecommerce'
     *              ],
     *              [
     *                  'locale' => 'en_US',
     *                  'scope' => 'mobile',
     *                  'data' => 'The description for mobile'
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
        $normalizedValues = json_decode($actions['normalized_values'], true);
        $currentLocale = $actions['current_locale'];
        $filteredValues = [];

        foreach ($normalizedValues as $attributeCode => $values) {
            /**
             * We don't call that method directly on the product model because it hydrates
             * lot of models and it causes memory leak...
             */
            if ($this->isAttributeEditable($product, $attributeCode)) {
                $values = array_filter($values, function ($value) use ($currentLocale) {
                    return $currentLocale === $value['locale'] || null === $value['locale'];
                });

                $filteredValues[$attributeCode] = $values;
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

    /**
     * @param ProductInterface $product
     * @param $attributeCode
     *
     * @return bool
     */
    protected function isAttributeEditable(ProductInterface $product, $attributeCode)
    {
        if (!$this->productRepository->hasAttributeInFamily($product->getId(), $attributeCode)) {
            return false;
        }

        if ($this->productRepository->hasAttributeInVariantGroup($product->getId(), $attributeCode)) {
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
            $this->getName(),
            'pim_enrich.mass_edit_action.edit-common-attributes.message.no_valid_attribute',
            [],
            [
                'class'  => ClassUtils::getClass($product),
                'id'     => $product->getId(),
                'string' => $product->getIdentifier()->getData(),
            ]
        );
    }
}
