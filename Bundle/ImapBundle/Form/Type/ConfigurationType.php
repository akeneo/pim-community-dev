<?php

namespace Oro\Bundle\ImapBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Oro\Bundle\ImapBundle\Entity\ImapEmailOrigin;
use Oro\Bundle\PlatformBundle\Security\Encryptor\Mcrypt;

class ConfigurationType extends AbstractType
{
    const NAME = 'oro_imap_configuration';

    /** @var Mcrypt */
    protected $encryptor;

    public function __construct(Mcrypt $encryptor)
    {
        $this->encryptor = $encryptor;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $encryptor = $this->encryptor;
        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) use ($encryptor) {
                $data = $event->getData();

                $filtered = array_filter(
                    $data,
                    function ($item) {
                        return !empty($item);
                    }
                );
                $data     = empty($filtered) ? null : $data;

                $oldPassword = $event->getForm()->get('password')->getData();
                if (empty($data['password']) && $oldPassword) {
                    // populate old password
                    $data['password'] = $oldPassword;
                } elseif (!empty($data['password'])) {
                    $data['password'] = $encryptor->encryptData($data['password']);
                }

                $event->setData($data);

                /** @var ImapEmailOrigin|null $entity */
                $entity = $event->getForm()->getData();
                if ($entity instanceof ImapEmailOrigin
                    && ($entity->getHost() != $data['host'] || $entity->getUser() != $data['user'])
                ) {
                    // in case when critical fields were changed new entity should be created
                    $newConfiguration = new ImapEmailOrigin();
                    $event->getForm()->setData($newConfiguration);

                    // deactivate old one
                    $entity->setIsActive(false);
                }
            }
        );

        $builder
            ->add('host', 'text', array('required' => true))
            ->add('port', 'number', array('required' => true))
            ->add(
                'ssl',
                'choice',
                array(
                    'choices'     => array('ssl' => 'ssl', 'tsl' => 'tsl'),
                    'empty_data'  => null,
                    'empty_value' => '',
                    'required'    => false
                )
            )
            ->add('user', 'text', array('required' => true))
            ->add('password', 'password', array('required' => true));
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Oro\\Bundle\\ImapBundle\\Entity\\ImapEmailOrigin',
                'required'   => false
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }
}
