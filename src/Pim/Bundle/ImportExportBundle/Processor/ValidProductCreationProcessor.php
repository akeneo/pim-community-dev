<?php

namespace Pim\Bundle\ImportExportBundle\Processor;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormFactoryInterface;
use Pim\Bundle\BatchBundle\Item\ItemProcessorInterface;
use Pim\Bundle\ImportExportBundle\AbstractConfigurableStepElement;
use Pim\Bundle\ImportExportBundle\Exception\InvalidObjectException;
use Pim\Bundle\ImportExportBundle\Validator\Constraints\Channel;
use Pim\Bundle\ProductBundle\Entity\Product;
use Pim\Bundle\ProductBundle\Manager\ProductManager;
use Pim\Bundle\ProductBundle\Manager\ChannelManager;
use Pim\Bundle\ImportExportBundle\Converter\ProductEnabledConverter;
use Pim\Bundle\ImportExportBundle\Converter\ProductFamilyConverter;
use Pim\Bundle\ImportExportBundle\Converter\ProductValueConverter;
use Pim\Bundle\ImportExportBundle\Converter\ProductCategoriesConverter;

/**
 * Product form processor
 * Allows to bind data into a product and validate them
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ValidProductCreationProcessor extends AbstractConfigurableStepElement implements ItemProcessorInterface
{
    protected $formFactory;
    protected $productManager;
    protected $channelManager;

    protected $enabled             = true;
    protected $categoriesColumn    = 'categories';
    protected $familyColumn        = 'family';

    /**
     * @Assert\NotBlank
     * @Channel
     */
    protected $channel;

    private $categories = array();
    private $attributes = array();

    public function __construct(
        FormFactoryInterface $formFactory,
        ProductManager $productManager,
        ChannelManager $channelManager
    ) {
        $this->formFactory    = $formFactory;
        $this->productManager = $productManager;
        $this->channelManager = $channelManager;
    }

    /**
     * Set wether or not the created product should be activated or not
     *
     * @param bool $enabled
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
     * Set the categories column
     *
     * @param string $categoriesColumn
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
     * @return null|Product
     *
     * @throw Exception when validation errors happenned
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
     * {@inheritDoc}
     */
    public function getConfigurationFields()
    {
        return array(
            'enabled'             => array(
                'type' => 'checkbox',
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
     * Create a product using the initialized attributes
     *
     * @param array $attributes
     * @param array $item
     *
     * @return Product
     */
    private function getProduct(array $item)
    {
        $product = $this->productManager->findByIdentifier(reset($item));
        if (!$product) {
            $product = $this->productManager->createFlexible();
        }

        return $product;
    }

    /**
     * Create and submit the product form
     *
     * @param Product     the product to which bind the data
     * @param array $item the processed item
     *
     * @return FormInterface
     */
    private function createAndSubmitForm(Product $product, array $item)
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

        if (array_key_exists($this->categoriesColumn, $item)) {
            $item[ProductCategoriesConverter::CATEGORIES_KEY] = $item[$this->categoriesColumn];
            unset($item[$this->categoriesColumn]);
        }

        $form->submit($item);

        return $form;
    }
}
