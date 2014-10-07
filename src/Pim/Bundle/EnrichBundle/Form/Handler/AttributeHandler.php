<?php

namespace Pim\Bundle\EnrichBundle\Form\Handler;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\CatalogBundle\Manager\AttributeManager;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

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
     * @var AttributeManager
     */
    protected $attributeManager;

    /**
     * Constructor for handler
     * @param FormInterface    $form             Form called
     * @param Request          $request          Web request
     * @param ObjectManager    $manager          Storage manager
     * @param AttributeManager $attributeManager Attribute manager
     */
    public function __construct(
        FormInterface $form,
        Request $request,
        ObjectManager $manager,
        AttributeManager $attributeManager
    ) {
        $this->form             = $form;
        $this->request          = $request;
        $this->manager          = $manager;
        $this->attributeManager = $attributeManager;
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
            $oldOptions = clone $entity->getOptions();
            $this->form->submit($this->request);

            if ($this->form->isValid()) {
                $this->onSuccess($entity, $oldOptions);

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
                $existingLocales = array();
                foreach ($option->getOptionValues() as $value) {
                    $existingLocales[] = $value->getLocale();
                }
                foreach ($locales as $locale) {
                    if (!in_array($locale, $existingLocales)) {
                        $optionValue = $this->attributeManager->createAttributeOptionValue();
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
        $selectTypes = array('pim_catalog_simpleselect', 'pim_catalog_multiselect');
        if (in_array($entity->getAttributeType(), $selectTypes) && count($entity->getOptions()) < 1) {
            $option = $this->attributeManager->createAttributeOption();
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
     * @param Collection         $oldOptions
     */
    protected function onSuccess(AttributeInterface $entity, Collection $oldOptions)
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

        // Manually remove if option is removed from entity
        foreach ($oldOptions as $oldOption) {
            if (false === $entity->getOptions()->contains($oldOption)) {
                $this->manager->remove($oldOption);
            }
        }

        $this->manager->persist($entity);
        $this->manager->flush();
    }
}
