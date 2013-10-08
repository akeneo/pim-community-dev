<?php

namespace Pim\Bundle\ImportExportBundle\Processor;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormFactoryInterface;
use Oro\Bundle\BatchBundle\Item\ItemProcessorInterface;
use Oro\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Pim\Bundle\ImportExportBundle\Exception\InvalidObjectException;
use Pim\Bundle\ImportExportBundle\Validator\Constraints\Channel;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\CatalogBundle\Manager\LocaleManager;
use Pim\Bundle\ImportExportBundle\Converter\ProductEnabledConverter;
use Pim\Bundle\ImportExportBundle\Converter\ProductFamilyConverter;
use Pim\Bundle\ImportExportBundle\Converter\ProductVariantGroupConverter;
use Pim\Bundle\ImportExportBundle\Converter\ProductValueConverter;
use Pim\Bundle\ImportExportBundle\Converter\ProductCategoriesConverter;

/**
 * Product form processor
 * Allows to bind data into a product and validate them
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ValidProductCreationProcessor extends AbstractConfigurableStepElement implements ItemProcessorInterface
{
    /**
     * @var FormFactoryInterface $formFactory
     */
    protected $formFactory;

    /**
     * @var ProductManager $productManager
     */
    protected $productManager;

    /**
     * @var ChannelManager $channelManager
     */
    protected $channelManager;

    /**
     * @var LocaleManager $localeManager
     */
    protected $localeManager;

    /**
     * @var boolean
     */
    protected $enabled = true;

    /**
     * @var string
     */
    protected $categoriesColumn = 'categories';

    /**
     * @var string
     */
    protected $familyColumn  = 'family';

    /**
     * @var string
     */
    protected $variantGroupColumn  = 'variant_group';

    /**
     * @Assert\NotBlank
     * @Channel
     */
    protected $channel;

    /**
     * Constructor
     *
     * @param FormFactoryInterface $formFactory
     * @param ProductManager       $productManager
     * @param ChannelManager       $channelManager
     * @param LocaleManager        $localeManager
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        ProductManager $productManager,
        ChannelManager $channelManager,
        LocaleManager $localeManager
    ) {
        $this->formFactory    = $formFactory;
        $this->productManager = $productManager;
        $this->channelManager = $channelManager;
        $this->localeManager  = $localeManager;
    }

    /**
     * Set wether or not the created product should be activated or not
     *
     * @param boolean $enabled
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
    }

    /**
     * Wether or not the created product should be activated or not
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * Set the categories column
     *
     * @param string $categoriesColumn
     */
    public function setCategoriesColumn($categoriesColumn)
    {
        $this->categoriesColumn = $categoriesColumn;
    }

    /**
     * Get the categories column
     *
     * @return string
     */
    public function getCategoriesColumn()
    {
        return $this->categoriesColumn;
    }

    /**
     * Set the family column
     *
     * @param string $familyColumn
     */
    public function setFamilyColumn($familyColumn)
    {
        $this->familyColumn = $familyColumn;
    }

    /**
     * Get the family column
     *
     * @return string
     */
    public function getFamilyColumn()
    {
        return $this->familyColumn;
    }

    /**
     * Set channel
     *
     * @param string $channel
     */
    public function setChannel($channel)
    {
        $this->channel = $channel;
    }

    /**
     * Get channel
     * @return string
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * Goal is to transform an array like this:
     * array(
     *     'sku'        => 'sku-001',
     *     'family'     => 'vehicle'
     *     'name-en_US' => 'car',
     *     'name-fr_FR' => 'voiture,
     *     'categories' => 'cat_1,cat_2,cat3',
     * )
     *
     * into this:
     * array(
     *    '[enabled]'    => true,
     *    '[family']'    => 'vehicle'
     *    'name-en_US'   => 'car',
     *    'name-fr_FR'   => 'voiture,
     *    '[categories]' => 'cat_1,cat_2,cat3',
     * )
     *
     * and to bind it to the ProductType.
     *
     * @param mixed $item item to be processed
     *
     * @return null|ProductInterface
     *
     * @throws Exception when validation errors happenned
     */
    public function process($item)
    {
        $product = $this->getProduct($item);
        $form    = $this->createAndSubmitForm($product, $item);

        if (!$form->isValid()) {
            throw new InvalidObjectException($form);
        }

        return $product;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return array(
            'enabled'             => array(
                'type' => 'switch',
            ),
            'categoriesColumn'    => array(),
            'familyColumn'        => array(),
            'channel'             => array(
                'type' => 'choice',
                'options' => array(
                    'choices'  => $this->channelManager->getChannelChoices(),
                    'required' => true
                )
            )
        );
    }

    /**
     * Find or create a product
     *
     * @param array $item
     *
     * @return Product
     */
    private function getProduct(array $item)
    {
        $product = $this->productManager->findByIdentifier(reset($item));
        if (!$product) {
            $product = $this->productManager->createProduct();
        } else {
            $product->getCategories()->count();
        }

        $product->setScope($this->channel);
        foreach (array_keys($item) as $code) {
            $locale = null;

            if (in_array($code, array($this->categoriesColumn, $this->familyColumn, $this->variantGroupColumn))) {
                continue;
            }

            if (strpos($code, '-')) {
                list($code, $locale) = explode('-', $code);
            }

            if ($locale) {
                $product->setLocale($locale);
            }

            if (false === $product->{'get'.ucfirst($code)}()) {
                $product->{'set'.ucfirst($code)}(null);
            }
        }

        return $product;
    }

    /**
     * Create and submit the product form
     *
     * @param ProductInterface $product the product to which bind the data
     * @param array            $item    the processed item
     *
     * @return FormInterface
     */
    private function createAndSubmitForm(ProductInterface $product, array $item)
    {
        $form = $this->formFactory->create(
            'pim_product',
            $product,
            array(
                'csrf_protection' => false,
                'import_mode'     => true,
            )
        );

        $item[ProductEnabledConverter::ENABLED_KEY] = $this->enabled;
        $item[ProductValueConverter::SCOPE_KEY]     = $this->channel;

        if (array_key_exists($this->familyColumn, $item)) {
            $item[ProductFamilyConverter::FAMILY_KEY] = $item[$this->familyColumn];
            unset($item[$this->familyColumn]);
        }

        if (array_key_exists($this->variantGroupColumn, $item)) {
            $item[ProductVariantGroupConverter::VARIANT_GROUP_KEY] = $item[$this->variantGroupColumn];
            unset($item[$this->variantGroupColumn]);
        }

        if (array_key_exists($this->categoriesColumn, $item)) {
            $item[ProductCategoriesConverter::CATEGORIES_KEY] = $item[$this->categoriesColumn];
            unset($item[$this->categoriesColumn]);
        }

        $values = $this->filterValues($product, $item);

        $form->submit($values);

        return $form;
    }

    /**
     * Filter imported values to avoid creating empty values for attributes not linked to the product or family
     *
     * @param ProductInterface $product
     * @param array            $values
     *
     * @return array
     */
    private function filterValues(ProductInterface $product, array $values)
    {
        if (array_key_exists(ProductFamilyConverter::FAMILY_KEY, $values)) {
            $familyCode = $values[ProductFamilyConverter::FAMILY_KEY];
        } else {
            $familyCode = null;
        }

        $requiredValues = $this->getRequiredValues($product, $familyCode);

        $excludedKeys = array(
            ProductEnabledConverter::ENABLED_KEY,
            ProductValueConverter::SCOPE_KEY,
            ProductFamilyConverter::FAMILY_KEY,
            ProductCategoriesConverter::CATEGORIES_KEY
        );

        foreach ($values as $key => $value) {
            if (!in_array($value, $excludedKeys) && $value === '' && !in_array($key, $requiredValues)) {
                unset($values[$key]);
            }
        }

        return $values;
    }

    /**
     * Get required values for a product based on the existing attributes and the family
     *
     * @param ProductInterface $product
     * @param string           $familyCode
     *
     * @return array
     */
    private function getRequiredValues(ProductInterface $product, $familyCode = null)
    {
        $requiredAttributes = array();

        if ($familyCode !== null) {
            $family = $this->productManager->getStorageManager()->getRepository('PimCatalogBundle:Family')->findOneBy(
                array(
                    'code' => $familyCode
                )
            );

            if ($family) {
                $requiredAttributes = $family->getAttributes()->toArray();
            }
        }

        if ($product->getId()) {
            foreach ($product->getValues() as $value) {
                if ($value->getId()) {
                    $requiredAttributes[] = $value->getAttribute();
                }
            }
        }

        if (empty($requiredAttributes)) {
            return array();
        }

        $requiredValues = array();

        $locales = $this->localeManager->getActiveCodes();

        foreach ($requiredAttributes as $attribute) {
            if ($attribute->getTranslatable()) {
                foreach ($locales as $locale) {
                    $requiredValues[] = sprintf('%s-%s', $attribute->getCode(), $locale);
                }
            } else {
                $requiredValues[] = $attribute->getCode();
            }
        }

        return array_unique($requiredValues);
    }
}
