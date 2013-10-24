<?php

namespace Pim\Bundle\ImportExportBundle\Processor;

use Symfony\Component\Form\FormFactoryInterface;
use Oro\Bundle\BatchBundle\Item\ItemProcessorInterface;
use Oro\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Oro\Bundle\BatchBundle\Step\StepExecutionAwareInterface;
use Oro\Bundle\BatchBundle\Entity\StepExecution;
use Pim\Bundle\ImportExportBundle\Exception\InvalidObjectException;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\CatalogBundle\Manager\LocaleManager;
use Pim\Bundle\ImportExportBundle\Converter\ProductErrorConverter;

/**
 * Product form processor
 * Allows to bind data into a product and validate them
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ValidProductCreationProcessor extends AbstractConfigurableStepElement implements ItemProcessorInterface,
 StepExecutionAwareInterface
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
    protected $groupsColumn  = 'groups';

    /**
     * Constructor
     *
     * @param FormFactoryInterface $formFactory
     * @param ProductManager       $productManager
     * @param LocaleManager        $localeManager
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        ProductManager $productManager,
        LocaleManager $localeManager
    ) {
        $this->formFactory    = $formFactory;
        $this->productManager = $productManager;
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
     * Set the groups column
     *
     * @param string $groupsColumn
     */
    public function setGroupsColumn($groupsColumn)
    {
        $this->groupsColumn = $groupsColumn;
    }

    /**
     * Get the categories column
     *
     * @return string
     */
    public function getGroupsColumn()
    {
        return $this->groupsColumn;
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

            $converter = new ProductErrorConverter();
            $warnings = $converter->convert($form);
            if (!empty($warnings)) {
                foreach ($warnings as $warning) {
                    $this->stepExecution->addFilterWarning(
                        get_class($this),
                        sprintf(
                            'Product %s : %s',
                            (string) $product->getIdentifier(),
                            $warning
                        ),
                        $item
                    );
                }

                return false;
            } else {
                throw new InvalidObjectException($form);
            }
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
            'groupsColumn'        => array(),
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
        }
        foreach (array_keys($item) as $key) {

            if (in_array($key, array($this->categoriesColumn, $this->familyColumn, $this->groupsColumn))) {
                continue;
            }

            list($code, $locale, $scope) = $this->parseProductValueKey($product, $key);

            if (false === $product->getValue($code, $locale, $scope)) {
                $value = $product->createValue($code, $locale, $scope);
                $product->addValue($value);
            }
        }

        return $product;
    }

    /**
     * Return attribute, locale and scope code
     *
     * @param Product $product
     * @param string  $key
     *
     * @return array
     */
    protected function parseProductValueKey($product, $key)
    {
        $tokens = explode('-', $key);
        $code   = $tokens[0];
        $locale = null;
        $scope  = null;

        $allAttributes = $product->getAllAttributes();
        if (!isset($allAttributes[$code])) {
            throw new \Exception(sprintf('Unknown attribute "%s"', $code));
        }
        $attribute = $allAttributes[$code];

        if ($attribute->getScopable() && $attribute->getTranslatable()) {
            if (count($tokens) < 3) {
                throw new \Exception(
                    sprintf(
                        'The column "%s" must contains attribute, locale and scope codes',
                        $key
                    )
                );
            }
            $locale = $tokens[1];
            $scope  = $tokens[2];
        } elseif ($attribute->getScopable()) {
            if (count($tokens) < 2) {
                throw new \Exception(
                    sprintf(
                        'The column "%s" must contains attribute and scope codes',
                        $key
                    )
                );
            }
            $scope = $tokens[1];
        } elseif ($attribute->getTranslatable()) {
            if (count($tokens) < 2) {
                throw new \Exception(
                    sprintf(
                        'The column "%s" must contains attribute and locale codes',
                        $key
                    )
                );
            }
            $locale = $tokens[1];
        }

        return array($code, $locale, $scope);
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
            'pim_product_import',
            $product,
            array(
                'family_column'     => $this->familyColumn,
                'categories_column' => $this->categoriesColumn,
                'groups_column'     => $this->groupsColumn,
            )
        );

        $item['enabled'] = $this->enabled;

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
        $familyCode = array_key_exists($this->familyColumn, $values)
            ? $values[$this->familyColumn]
            : null;

        $groupCodes = array_key_exists($this->groupsColumn, $values)
            ? $values[$this->groupsColumn]
            : null;

        $requiredValues = $this->getRequiredValues($product, $familyCode, $groupCodes);

        $excludedKeys = array(
            'enabled',
            $this->familyColumn,
            $this->categoriesColumn,
            $this->groupsColumn
        );

        foreach ($values as $key => $value) {
            if (!in_array($value, $excludedKeys) && $value === '' && !in_array($key, $requiredValues)) {
                unset($values[$key]);
            }
        }

        return $values;
    }

    /**
     * Get required values for a product based on the existing attributes, the family and the groups
     *
     * @param ProductInterface $product
     * @param string           $familyCode
     * @param string           $groupCodes
     *
     * @return array
     */
    private function getRequiredValues(ProductInterface $product, $familyCode = null, $groupCodes = null)
    {
        $requiredAttributes = array();
        $storageManager = $this->productManager->getStorageManager();

        if ($familyCode !== null) {
            $family = $storageManager->getRepository('PimCatalogBundle:Family')->findOneBy(
                array(
                    'code' => $familyCode
                )
            );

            if ($family) {
                $requiredAttributes = $family->getAttributes()->toArray();
            }
        }

        if ($groupCodes !== null) {
            $groupCodes = explode(',', $groupCodes);
            foreach ($groupCodes as $code) {
                $group = $storageManager->getRepository('PimCatalogBundle:Group')->findOneBy(
                    array(
                        'code' => $code
                    )
                );

                if ($group) {
                    $requiredAttributes = array_merge($requiredAttributes, $group->getAttributes()->toArray());
                }
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

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }
}
