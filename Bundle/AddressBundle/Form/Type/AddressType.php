<?php

namespace Oro\Bundle\AddressBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\AbstractType;

use Oro\Bundle\AddressBundle\Form\EventListener\BuildAddressFormListener;

class AddressType extends AbstractType
{
    /**
     * @var BuildAddressFormListener
     */
    private $eventListener;

    /**
     * @param BuildAddressFormListener $eventListener
     */
    public function __construct(BuildAddressFormListener $eventListener)
    {
        $this->eventListener = $eventListener;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventSubscriber($this->eventListener);

        $builder
            ->add('id', 'hidden')
            ->add('label', 'text', array('required' => false, 'label' => 'Label'))
            ->add('firstName', 'text', array('required' => false, 'label' => 'First Name'))
            ->add('lastName', 'text', array('required' => false, 'label' => 'Last Name'))
            ->add('street', 'text', array('required' => true, 'label' => 'Street'))
            ->add('street2', 'text', array('required' => false, 'label' => 'Street 2'))
            ->add('city', 'text', array('required' => true, 'label' => 'City'))
            ->add('state', 'oro_region', array('required' => false, 'label' => 'State'))
            ->add('state_text', 'hidden', array('required' => false, 'label' => 'Custom State'))
            ->add('country', 'oro_country', array('required' => true, 'label' => 'Country'))
            ->add('postalCode', 'text', array('required' => true, 'label' => 'ZIP/Postal code'));
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class'           => 'Oro\Bundle\AddressBundle\Entity\Address',
                'intention'            => 'address',
                'extra_fields_message' => 'This form should not contain extra fields: "{{ extra_fields }}"',
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_address';
    }
}
