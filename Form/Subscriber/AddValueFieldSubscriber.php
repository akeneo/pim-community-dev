<?php
namespace Pim\Bundle\ProductBundle\Form\Subscriber;

use Oro\Bundle\FlexibleEntityBundle\Form\EventListener\AddValueFieldSubscriber as OroAddValueFieldSubscriber;

use Symfony\Component\Form\FormEvent;

/**
 * Extends Oro subscriber
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class AddValueFieldSubscriber extends OroAddValueFieldSubscriber
{

    /**
     * Add form field type
     * @param FormEvent $event
     */
    public function preSetData(FormEvent $event)
    {
        $value = $event->getData();
        $form = $event->getForm();

        // skip form creation with no data
        if (null === $value) {
            return;
        }

        $attribute     = $value->getAttribute();
        $attributeType = $this->flexibleManager->getAttributeTypeFactory()->create($attribute->getAttributeType());
        $valueForm = $attributeType->buildValueFormType($this->factory, $value);
        $form->add($valueForm);
        
        /* TODO : how to add this kind of rules (wysiwyg) without property management ?
        $attribute     = $value->getAttribute();
        $attributeType = $this->flexibleManager->getAttributeTypeFactory()->create($attribute->getAttributeType());
 
        $formName    = $attribute->getBackendType();
        $formType    = $attributeType->getFormType();
        $formOptions = $attributeType->prepareFormOptions($attribute);
        $data        = is_null($value->getData()) ? $attribute->getDefaultValue() : $value->getData();

        // add business rules
        if ($formType === 'textarea' && $attribute->getWysiwygEnabled()) {
            $formType = 'pim_wysiwyg';
        }

        $form->add($this->factory->createNamed($formName, $formType, $data, $formOptions));
*/
    }
}
