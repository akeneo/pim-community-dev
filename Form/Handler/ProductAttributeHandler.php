<?php
namespace Pim\Bundle\ProductBundle\Form\Handler;

use Pim\Bundle\ProductBundle\Entity\ProductAttribute;
use Pim\Bundle\ProductBundle\Entity\AttributeOptionValue;
use Pim\Bundle\ProductBundle\Service\AttributeService;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormInterface;

/**
 * Form handler for Product attribute
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
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
     * @var AttributeService
     */
    protected $service;

    /**
     * Constructor for handler
     * @param FormInterface    $form    Form called
     * @param Request          $request Web request
     * @param ObjectManager    $manager Storage manager
     * @param AttributeService $service Attribute service
     */
    public function __construct(FormInterface $form, Request $request, ObjectManager $manager, AttributeService $service)
    {
        $this->form    = $form;
        $this->request = $request;
        $this->manager = $manager;
        $this->service = $service;
    }

    /**
     * Preprocess method
     * @param ProductAttribute $data
     */
    public function preProcess($data)
    {
        $attribute = $this->service->createAttributeFromFormData($data);

        $this->form->setData($attribute);

        $data = $this->service->prepareFormData($data);

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
        $languages = $this->manager->getRepository('PimConfigBundle:Language')->findBy(array('activated' => 1));
        $locales = array();
        foreach ($languages as $language) {
            $locales[] = $language->getCode();
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
                        $optionValue->setlocale($locale);
                        $optionValue->setValue('');
                        $option->addOptionValue($optionValue);
                    }
                }
            }
        }
        $this->form->setData($entity);

        if ($this->request->getMethod() === 'POST') {
            $this->form->bind($this->request);

            if ($this->form->isValid()) {
                $this->onSuccess($entity);

                return true;
            }
        }

        return false;
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
        $this->manager->persist($entity);
        $this->manager->flush();
    }
}
