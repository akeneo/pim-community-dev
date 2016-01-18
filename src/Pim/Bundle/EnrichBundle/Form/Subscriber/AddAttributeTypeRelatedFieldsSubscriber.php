<?php

namespace Pim\Bundle\EnrichBundle\Form\Subscriber;

use Oro\Bundle\SecurityBundle\SecurityFacade;
use Pim\Bundle\CatalogBundle\AttributeType\AttributeTypeRegistry;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeGroupRepositoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

/**
 * Form subscriber for AttributeInterface
 * Allow to change field behavior like disable when editing
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddAttributeTypeRelatedFieldsSubscriber implements EventSubscriberInterface
{
    /** @var AttributeTypeRegistry */
    protected $attTypeFactory;

    /** @var FormFactoryInterface */
    protected $factory;

    /** @var SecurityFacade */
    protected $securityFacade;

    /** @var AttributeGroupRepositoryInterface */
    protected $groupRepository;

    /**
     * Constructor
     *
     * @param AttributeTypeRegistry             $attTypeRegistry Registry
     * @param SecurityFacade                    $securityFacade
     * @param AttributeGroupRepositoryInterface $groupRepository
     */
    public function __construct(
        AttributeTypeRegistry $attTypeRegistry,
        SecurityFacade $securityFacade,
        AttributeGroupRepositoryInterface $groupRepository
    ) {
        $this->attTypeRegistry = $attTypeRegistry;
        $this->securityFacade  = $securityFacade;
        $this->groupRepository = $groupRepository;
    }

    /**
     * Set form factory
     *
     * @param FormFactoryInterface $factory
     */
    public function setFactory(FormFactoryInterface $factory = null)
    {
        $this->factory = $factory;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SET_DATA => 'preSetData'
        ];
    }

    /**
     * Method called before set data
     *
     * @param FormEvent $event
     */
    public function preSetData(FormEvent $event)
    {
        $data = $event->getData();
        if (null === $data) {
            return;
        }

        if (is_null($data->getId()) === false) {
            $form = $event->getForm();
            $this->disableField($form, 'code');
        }

        if (!$this->securityFacade->isGranted('pim_enrich_attributegroup_add_attribute')) {
            $form = $event->getForm();
            $this->hideGroupElement($form, $data);
        }

        $this->customizeForm($event->getForm(), $data);
    }

    /**
     * Customize the attribute form
     *
     * @param Form               $form
     * @param AttributeInterface $attribute
     */
    protected function customizeForm(Form $form, AttributeInterface $attribute)
    {
        $attTypeClass = $this->attTypeRegistry->get($attribute->getAttributeType());
        $fields       = $attTypeClass->buildAttributeFormTypes($this->factory, $attribute);

        foreach ($fields as $field) {
            $form->add($field);
        }
    }

    /**
     * Disable a field from its name
     *
     * @param Form   $form Form
     * @param string $name Field name
     */
    protected function disableField(Form $form, $name)
    {
        // get form field and properties
        $formField = $form->get($name);
        $type      = $formField->getConfig()->getType();
        $options   = $formField->getConfig()->getOptions();

        // replace by disabled and read-only
        $options['disabled']        = true;
        $options['read_only']       = true;
        $options['auto_initialize'] = false;
        $formField = $this->factory->createNamed($name, $type, null, $options);
        $form->add($formField);
    }

    /**
     * Hide the group field with a default value = "Other"
     *
     * @param FormInterface      $form Form
     * @param AttributeInterface $data
     */
    protected function hideGroupElement(FormInterface $form, AttributeInterface $data)
    {
        if (null !== $data->getId()) {
            $group = $data->getGroup();
        } else {
            $group = $this->groupRepository->findDefaultAttributeGroup();
        }

        $formField = $form->get('group');
        $options = $formField->getConfig()->getOptions();

        $newOptions =            [
            'data'      => $group,
            'class'     => $options['class'],
            'choices'   => [$group],
            'required'  => true,
            'multiple'  => false,
            'read_only' => true,
            'attr'      => [
                'class' => 'hide'
            ]
        ];

        $form->add(
            'group',
            'entity',
            $newOptions
        );
    }
}
