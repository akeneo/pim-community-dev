<?php

namespace Pim\Bundle\EnrichBundle\Connector\Processor\MassEdit\Product;

use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\Component\StorageUtils\Updater\PropertySetterInterface;
use Pim\Bundle\CatalogBundle\Exception\InvalidArgumentException;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\ProductMassActionRepositoryInterface;
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

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var PropertySetterInterface */
    protected $propertySetter;

    /** @var array */
    protected $skippedAttributes = [];

    /** @var ObjectUpdaterInterface */
    protected $productUpdater;

    /**
     * @param PropertySetterInterface              $propertySetter
     * @param ValidatorInterface                   $validator
     * @param ProductMassActionRepositoryInterface $massActionRepository
     * @param AttributeRepositoryInterface         $attributeRepository
     * @param JobConfigurationRepositoryInterface  $jobConfigurationRepo
     */
    public function __construct(
        PropertySetterInterface $propertySetter,
        ValidatorInterface $validator,
        ProductMassActionRepositoryInterface $massActionRepository,
        AttributeRepositoryInterface $attributeRepository,
        JobConfigurationRepositoryInterface $jobConfigurationRepo,
        ObjectUpdaterInterface $productUpdater
    ) {
        parent::__construct($jobConfigurationRepo);

        $this->propertySetter      = $propertySetter;
        $this->validator           = $validator;
        $this->attributeRepository = $attributeRepository;
        $this->productUpdater      = $productUpdater;
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

            return null;
        }

        $actions = $configuration['actions'];

        $product = $this->updateProduct($product, $actions);
        if (null !== $product && !$this->isProductValid($product)) {
            $this->stepExecution->incrementSummaryInfo('skipped_products');

            return null;
        }

        return $product;
    }

    /**
     * Set data from $actions to the given $product
     *
     * Actions should looks like that
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
        $filteredValues = [];

        foreach ($normalizedValues as $attributeCode => $values) {
            $attribute = $this->attributeRepository->findOneBy(['code' => $attributeCode]);

            if ($product->isAttributeEditable($attribute)) {
                $filteredValues[$attributeCode] = $values;
            }
        }

        if (count($filteredValues) > 0) {
            $this->productUpdater->update($product, $filteredValues);
        } else {
            $this->stepExecution->incrementSummaryInfo('skipped_products');
            $this->stepExecution->addWarning(
                $this->getName(),
                'pim_enrich.mass_edit_action.edit-common-attributes.message.no_valid_attribute',
                [],
                $product
            );

            return null;
        }

        return $product;
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
}
