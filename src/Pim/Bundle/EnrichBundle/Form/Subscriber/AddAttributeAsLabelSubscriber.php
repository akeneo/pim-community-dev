<?php

namespace Pim\Bundle\EnrichBundle\Form\Subscriber;

use Oro\Bundle\SecurityBundle\SecurityFacade;
use Pim\Bundle\CatalogBundle\Model\FamilyInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * Add useable attributes as labels
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddAttributeAsLabelSubscriber implements EventSubscriberInterface
{
    /** @var FormFactoryInterface */
    protected $factory;

    /** @var string */
    protected $attributeClass;

    /** @var SecurityFacade */
    protected $securityFacade;

    /**
     * Constructor
     *
     * @param string               $attributeClass
     * @param FormFactoryInterface $factory
     * @param SecurityFacade       $securityFacade
     */
    public function __construct($attributeClass, FormFactoryInterface $factory, SecurityFacade $securityFacade)
    {
        $this->attributeClass = $attributeClass;
        $this->factory        = $factory;
        $this->securityFacade = $securityFacade;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(FormEvents::PRE_SET_DATA => 'addAttributeAsLabelField');
    }

    /**
     * @param FormEvent $event
     */
    public function addAttributeAsLabelField(FormEvent $event)
    {
        $data = $event->getData();

        if ($data instanceof FamilyInterface && $data->getId()) {
            $form = $event->getForm();
            $form->add(
                $this->factory->createNamed(
                    'attributeAsLabel',
                    'entity',
                    $data->getAttributeAsLabel(),
                    array(
                        'required'        => true,
                        'label'           => 'Attribute used as label',
                        'class'           => $this->attributeClass,
                        'choices'         => $data->getAttributeAsLabelChoices(),
                        'auto_initialize' => false,
                        'select2'         => true,
                        'disabled'        => !$this->securityFacade->isGranted('pim_enrich_family_edit_properties'),
                    )
                )
            );
        }
    }
}
