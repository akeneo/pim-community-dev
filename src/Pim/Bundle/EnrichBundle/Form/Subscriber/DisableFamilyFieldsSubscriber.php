<?php

namespace Pim\Bundle\EnrichBundle\Form\Subscriber;

use Oro\Bundle\SecurityBundle\SecurityFacade;
use Pim\Bundle\CatalogBundle\Model\FamilyInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

/**
 * Subscriber to disable fields of the properties tab in the family form
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DisableFamilyFieldsSubscriber implements EventSubscriberInterface
{
    /** @var SecurityFacade */
    protected $securityFacade;

    /** @var array */
    protected $propertyFields = ['code', 'label'];

    /** @var array */
    protected $attributeFields = ['attributes', 'attributeRequirements'];

    /**
     * @param SecurityFacade $securityFacade
     */
    public function __construct(SecurityFacade $securityFacade)
    {
        $this->securityFacade = $securityFacade;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::POST_SET_DATA => 'postSetData'
        );
    }

    /**
     * Disable the fields
     *
     * @param FormEvent $event
     */
    public function postSetData(FormEvent $event)
    {
        if (!$event->getData() instanceof FamilyInterface || null === $event->getData()->getId()) {
            return;
        }

        $propertiesGranted = $this->securityFacade->isGranted('pim_enrich_family_edit_properties');
        $attributesGranted = $this->securityFacade->isGranted('pim_enrich_family_edit_attributes');

        if (!$propertiesGranted || !$attributesGranted) {
            $form = $event->getForm();
            foreach ($form as $field) {
                if ((!$propertiesGranted && in_array($field->getName(), $this->propertyFields)) ||
                    (!$attributesGranted && in_array($field->getName(), $this->attributeFields))) {
                    $this->disableField($field);
                }
            }
        }
    }

    /**
     * Disable a field after the form has been created
     *
     * @param FormInterface $field
     */
    protected function disableField(FormInterface $field)
    {
        $config = $field->getConfig();
        $options = $config->getOptions();
        $options['disabled'] = true;
        $field
            ->getParent()
            ->add(
                $field->getName(),
                $config->getType()->getInnerType(),
                $options
            );
    }
}
