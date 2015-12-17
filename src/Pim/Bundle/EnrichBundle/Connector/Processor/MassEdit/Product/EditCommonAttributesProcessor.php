<?php

namespace Pim\Bundle\EnrichBundle\Connector\Processor\MassEdit\Product;

use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Pim\Bundle\EnrichBundle\Connector\Processor\AbstractProcessor;
use Pim\Component\Catalog\Exception\InvalidArgumentException;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Connector\Repository\JobConfigurationRepositoryInterface;
use Pim\Component\Localization\Localizer\LocalizerRegistryInterface;
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

    /** @var array */
    protected $skippedAttributes = [];

    /** @var LocalizerRegistryInterface */
    protected $localizerRegistry;

    /** @var ObjectUpdaterInterface */
    protected $productUpdater;

    /**
     * @param ValidatorInterface                  $validator
     * @param AttributeRepositoryInterface        $attributeRepository
     * @param JobConfigurationRepositoryInterface $jobConfigurationRepo
     * @param LocalizerRegistryInterface          $localizerRegistry
     * @param ObjectUpdaterInterface              $productUpdater
     */
    public function __construct(
        ValidatorInterface $validator,
        AttributeRepositoryInterface $attributeRepository,
        JobConfigurationRepositoryInterface $jobConfigurationRepo,
        LocalizerRegistryInterface $localizerRegistry,
        ObjectUpdaterInterface $productUpdater
    ) {
        parent::__construct($jobConfigurationRepo);

        $this->validator           = $validator;
        $this->attributeRepository = $attributeRepository;
        $this->localizerRegistry   = $localizerRegistry;
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

        $product = $this->updateProduct($product, $configuration['actions']);
        if (null !== $product && !$this->isProductValid($product)) {
            $this->stepExecution->incrementSummaryInfo('skipped_products');

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
        $values = $this->prepareProductValues($product, $actions);

        if (empty($values)) {
            $this->stepExecution->incrementSummaryInfo('skipped_products');
            $this->stepExecution->addWarning(
                $this->getName(),
                'pim_enrich.mass_edit_action.edit-common-attributes.message.no_valid_attribute',
                [],
                $product
            );

            return null;
        }

        $this->productUpdater->update($product, $values);

        return $product;
    }

    /**
     * Prepare product values
     *
     * @param ProductInterface $product
     * @param array            $actions
     *
     * @return array
     */
    protected function prepareProductValues(ProductInterface $product, array $actions)
    {
        $normalizedValues = json_decode($actions['normalized_values'], true);
        $attributeLocale = $actions['attribute_locale'];
        $filteredValues = [];

        foreach ($normalizedValues as $attributeCode => $values) {
            $attribute = $this->attributeRepository->findOneByIdentifier($attributeCode);

            if ($product->isAttributeEditable($attribute)) {
                $values = array_filter($values, function ($value) use ($attributeLocale) {
                    return $attributeLocale === $value['locale'] || null === $value['locale'];
                });

                $localizer = $this->localizerRegistry->getLocalizer($attribute->getAttributeType());
                if (null !== $localizer) {
                    foreach ($values as $value) {
                        $value['data'] = $localizer->delocalize($value['data'], [
                            'locale' => $actions['ui_locale']
                        ]);
                    }
                }

                $filteredValues[$attributeCode] = $values;
            }
        }

        return $filteredValues;
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
