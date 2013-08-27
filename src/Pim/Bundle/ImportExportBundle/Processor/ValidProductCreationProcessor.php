<?php

namespace Pim\Bundle\ImportExportBundle\Processor;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormFactoryInterface;
use Doctrine\ORM\EntityManager;
use Pim\Bundle\BatchBundle\Item\ItemProcessorInterface;
use Pim\Bundle\ImportExportBundle\AbstractConfigurableStepElement;
use Pim\Bundle\ImportExportBundle\Exception\InvalidObjectException;
use Pim\Bundle\ImportExportBundle\Validator\Constraints\Channel;
use Pim\Bundle\ProductBundle\Entity\Product;
use Pim\Bundle\ProductBundle\Entity\ProductAttribute;
use Pim\Bundle\ProductBundle\Manager\ProductManager;
use Pim\Bundle\ProductBundle\Manager\ChannelManager;

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
    protected $entityManager;
    protected $formFactory;
    protected $productManager;
    protected $channelManager;

    protected $enabled             = true;
    protected $categoriesColumn    = 'categories';
    protected $categoriesDelimiter = ',';
    protected $familyColumn        = 'family';

    /**
     * @Assert\NotBlank
     * @Channel
     */
    protected $channel;

    private $categories = array();
    private $attributes = array();

    public function __construct(
        EntityManager $entityManager,
        FormFactoryInterface $formFactory,
        ProductManager $productManager,
        ChannelManager $channelManager
    ) {
        $this->entityManager  = $entityManager;
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
     * Get the categories delimiter
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
     * Get the family delimiter
     *
     * @return string
     */
    public function getFamilyColumn()
    {
        return $this->familyColumn;
    }

    /**
     * Set the categories delimiter
     *
     * @param string $categoriesDelimiter
     */
    public function setCategoriesDelimiter($categoriesDelimiter)
    {
        $this->categoriesDelimiter = $categoriesDelimiter;
    }

    /**
     * Get the categories delimiter
     *
     * @return string
     */
    public function getCategoriesDelimiter()
    {
        return $this->categoriesDelimiter;
    }

    /**
     * Set channel
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
     *     'name-en_US' => 'car',
     *     'name-fr_FR' => 'voiture,
     *     'categories' => 'cat_1,cat_2,cat3',
     * )
     *
     * into this:
     * array(
     *    'enabled' => '1',
     *    'values'  => array(
     *        'sku' => array(
     *             'varchar' => 'sku-001',
     *         ),
     *         'name_en_US' => array(
     *             'varchar' => 'car'
     *         ),
     *         'name_fr_FR' => array(
     *             'varchar' => 'voiture'
     *         ),
     *     ),
     *     'categories' => array(1, 2, 3)
     * )
     *
     * and to bind it to the ProductType.
     *
     * @param mixed $item item to be processed
     *
     * @return null|Product
     *
     * @throws Exception when validation errors happenned
     */
    public function process($item)
    {
        foreach (array_keys($item) as $code) {
            $attributes[$code] = $this->getAttribute($code);
        }

        $product = $this->getProduct($attributes, $item);
        $form    = $this->createAndSubmitForm($product, $attributes, $item);

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
                'type' => 'checkbox',
            ),
            'categoriesColumn'    => array(),
            'categoriesDelimiter' => array(),
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
    private function getProduct(array $attributes, array $item)
    {
        $product = $this->productManager->findByIdentifier(reset($item));
        if (!$product) {
            $product = $this->productManager->createFlexible();
            foreach ($attributes as $code => $attribute) {
                if (!$attribute || false !== $product->{'get'.ucfirst($code)}()) {
                    continue;
                }

                if ($attribute->getScopable()) {
                    $product->setScope($this->channel);
                }

                if ($attribute->getTranslatable()) {
                    list($code, $locale) = explode('-', $code);
                    $product->setLocale($locale);
                }

                $product->{'set'.ucfirst($code)}(null);
            }
        }

        return $product;
    }

    private function getValue(ProductAttribute $attribute, $value)
    {
        switch ($attribute->getBackendType()) {
            case 'prices':
                foreach (explode(',', $value) as $price) {
                    list($data, $currency) = explode(' ', $price);
                    $prices[] = array(
                        'data'     => $data,
                        'currency' => $currency,
                    );
                }

                return array(
                    'prices' => $prices
                );
            case 'date':
                $date = new \DateTime($value);

                return array($attribute->getBackendType() => $date->format('m/d/Y'));
            case 'option':
                if ($option = $this->getOption($value)) {
                    return array($attribute->getBackendType() => $option->getId());
                }

                return array();
            case 'options':
                $options = array();
                foreach (explode(',', $value) as $val) {
                    if ($option = $this->getOption($val)) {
                        $options[] = $option->getId();
                    }
                }

                return array($attribute->getBackendType() => $options);
            default:
                return array($attribute->getBackendType() => $value);
        }
    }

    private function getAttributeCode(array $attributes, $code)
    {
        $attribute = $attributes[$code];
        $suffix = $attribute->getScopable() ? sprintf('_%s', $this->channel) : '';

        return str_replace('-', '_', $code).$suffix;
    }

    /**
     * Create and submit the product form
     *
     * @param Product     the product to which bind the data
     * @param array $item the processed item
     *
     * @return FormInterface
     */
    private function createAndSubmitForm(Product $product, array $attributes, array $item)
    {
        $values     = array();
        $categories = array();
        $family     = null;
        foreach ($item as $code => $value) {
            if (isset($attributes[$code])) {
                $values[$this->getAttributeCode($attributes, $code)] = $this->getValue($attributes[$code], $value);
            } elseif ($code === $this->categoriesColumn) {
                $categories = $this->getCategoryIds($value);
            } elseif ($code === $this->familyColumn) {
                $family = $this->getFamilyId($value);
            }
        }

        $form = $this->formFactory->create(
            'pim_product',
            $product,
            array(
                'csrf_protection' => false,
                'import_mode'     => true,
            )
        );

        $data = array(
            'enabled'    => (string) (int) $this->enabled,
            'values'     => $values,
            'categories' => $categories,
            'family'     => $family,
        );
        $form->submit($data);

        return $form;
    }

    private function getCategoryIds($codes)
    {
        $ids = array();
        foreach (explode($this->categoriesDelimiter, $codes) as $code) {
            if ($category = $this->getCategory($code)) {
                $ids[] = $category->getId();
            }
        }

        return $ids;
    }

    public function getFamilyId($code)
    {
        if ($family = $this->getFamily($code)) {
            return $family->getId();
        }
    }

    private function getFamily($code)
    {
        return $this->entityManager
            ->getRepository('PimProductBundle:Family')
            ->findOneBy(array('code' => $code));
    }

    private function getAttribute($code)
    {
        $parts = explode('-', $code);

        if (!array_key_exists($parts[0], $this->attributes)) {
            $this->attributes[$parts[0]] = $this->entityManager
                ->getRepository('PimProductBundle:ProductAttribute')
                ->findOneBy(array('code' => $parts[0]));
        }

        return $this->attributes[$parts[0]];
    }

    private function getCategory($code)
    {
        if (!array_key_exists($code, $this->categories)) {
            $this->categories[$code] = $this->entityManager
                ->getRepository('PimProductBundle:Category')
                ->findOneBy(array('code' => $code));
        }

        return $this->categories[$code];
    }

    private function getOption($code)
    {
        return $this->entityManager
            ->getRepository('PimProductBundle:AttributeOption')
            ->findOneBy(array('code' => $code));
    }
}
