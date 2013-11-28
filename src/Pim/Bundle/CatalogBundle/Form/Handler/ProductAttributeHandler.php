<?php

namespace Pim\Bundle\CatalogBundle\Form\Handler;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\CatalogBundle\Entity\ProductAttribute;
use Pim\Bundle\CatalogBundle\Entity\AttributeOption;
use Pim\Bundle\CatalogBundle\Entity\AttributeOptionValue;
use Pim\Bundle\CatalogBundle\Manager\AttributeTypeManager;

/**
 * Form handler for Product attribute
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductAttributeHandler
{
    /**
     * @var FormInterface
     */
    protected $form;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var ObjectManager
     */
    protected $manager;

    /**
     * @var AttributeTypeManager
     */
    protected $attTypeManager;

    /**
     * Constructor for handler
     * @param FormInterface        $form           Form called
     * @param Request              $request        Web request
     * @param ObjectManager        $manager        Storage manager
     * @param AttributeTypeManager $attTypeManager Attribute type manager
     */
    public function __construct(
        FormInterface $form,
        Request $request,
        ObjectManager $manager,
        AttributeTypeManager $attTypeManager
    ) {
        $this->form    = $form;
        $this->request = $request;
        $this->manager = $manager;
        $this->attTypeManager = $attTypeManager;
    }

    /**
     * Preprocess method
     * @param ProductAttribute $data
     */
    public function preProcess($data)
    {
        $attribute = $this->attTypeManager->createAttributeFromFormData($data);

        $this->form->setData($attribute);

        $data = $this->attTypeManager->prepareFormData($data);

        $this->form->bind($data);
    }

    /**
     * Process method for handler
     * @param ProductAttribute $entity
     *
     * @return boolean
     */
    public function process(ProductAttribute $entity)
    {
        $this->addMissingOptionValues($entity);
        $this->form->setData($entity);

        if ($this->request->isMethod('POST')) {
            $this->form->bind($this->request);

            if ($this->form->isValid()) {
                $this->onSuccess($entity);

                return true;
            }
        }

        return false;
    }

    /**
     * Add missing attribute option values
     *
     * @param ProductAttribute $entity
     */
    protected function addMissingOptionValues(ProductAttribute $entity)
    {
        $locales = $this->getLocaleCodes();

        if (in_array($entity->getAttributeType(), array('pim_catalog_simpleselect', 'pim_catalog_multiselect')) &&
            count($entity->getOptions()) < 1) {
            $option = new AttributeOption();
            $option->setTranslatable(true);
            $entity->addOption($option);
        }

        foreach ($entity->getOptions() as $option) {
            if ($option->getTranslatable()) {
                $existingLocales = array();
                foreach ($option->getOptionValues() as $value) {
                    $existingLocales[] = $value->getLocale();
                }
                foreach ($locales as $locale) {
                    if (!in_array($locale, $existingLocales)) {
                        $optionValue = new AttributeOptionValue();
                        $optionValue->setLocale($locale);
                        $optionValue->setValue('');
                        $option->addOptionValue($optionValue);
                    }
                }
            }
        }
    }

    /**
     * Get activated locale codes
     *
     * @return array
     */
    protected function getLocaleCodes()
    {
        $locales = array_map(
            function ($locale) {
                return $locale->getCode();
            },
            $this->manager->getRepository('PimCatalogBundle:Locale')->getActivatedLocales()
        );

        return $locales;
    }

    /**
     * Call when form is valid
     * @param ProductAttribute $entity
     */
    protected function onSuccess(ProductAttribute $entity)
    {
        foreach ($entity->getOptions() as $option) {
            // Setting translatable to true for now - option not implemented in UI
            $option->setTranslatable(true);
            // Validation not implemented yet - this should probably be checked there
            if (!$option->getSortOrder()) {
                $option->setSortOrder(1);
            }
            foreach ($option->getOptionValues() as $optionValue) {
                if (!$optionValue->getValue()) {
                    $option->removeOptionValue($optionValue);
                }
            }
        }

        $this->attTypeManager->prepareBackendProperties($entity);

        $this->manager->persist($entity);
        $this->manager->flush();
    }
}
