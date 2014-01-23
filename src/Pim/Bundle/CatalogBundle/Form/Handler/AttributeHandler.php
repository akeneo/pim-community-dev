<?php

namespace Pim\Bundle\CatalogBundle\Form\Handler;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Entity\AttributeOption;
use Pim\Bundle\CatalogBundle\Entity\AttributeOptionValue;
use Pim\Bundle\CatalogBundle\Manager\AttributeManagerInterface;

/**
 * Form handler for attribute
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeHandler
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
     * @var AttributeManagerInterface
     */
    protected $attributeManager;

    /**
     * Constructor for handler
     * @param FormInterface             $form             Form called
     * @param Request                   $request          Web request
     * @param ObjectManager             $manager          Storage manager
     * @param AttributeManagerInterface $attributeManager Attribute type manager
     */
    public function __construct(
        FormInterface $form,
        Request $request,
        ObjectManager $manager,
        AttributeManagerInterface $attributeManager
    ) {
        $this->form    = $form;
        $this->request = $request;
        $this->manager = $manager;
        $this->attributeManager = $attributeManager;
    }

    /**
     * Preprocess method
     * @param AttributeInterface $data
     */
    public function preProcess($data)
    {
        $attribute = $this->attributeManager->createAttributeFromFormData($data);

        $this->form->setData($attribute);

        $data = $this->attributeManager->prepareFormData($data);

        $this->form->bind($data);
    }

    /**
     * Process method for handler
     * @param AttributeInterface $entity
     *
     * @return boolean
     */
    public function process(AttributeInterface $entity)
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
     * @param AttributeInterface $entity
     */
    protected function addMissingOptionValues(AttributeInterface $entity)
    {
        $this->ensureOneOption($entity);

        $locales = $this->getLocaleCodes();
        foreach ($entity->getOptions() as $option) {
            if ($option->isTranslatable()) {
                $existingLocales = [];
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
     * Ensure at least one option for the attribute
     *
     * @param AttributeInterface $entity
     */
    protected function ensureOneOption(AttributeInterface $entity)
    {
        $selectTypes = ['pim_catalog_simpleselect', 'pim_catalog_multiselect'];
        if (in_array($entity->getAttributeType(), $selectTypes) && count($entity->getOptions()) < 1) {
            $option = new AttributeOption();
            $option->setTranslatable(true);
            $entity->addOption($option);
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
     * @param AttributeInterface $entity
     */
    protected function onSuccess(AttributeInterface $entity)
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

        $this->attributeManager->prepareBackendProperties($entity);

        $this->manager->persist($entity);
        $this->manager->flush();
    }
}
