<?php

namespace Oro\Bundle\UserBundle\Form\Type;

use Oro\Bundle\UserBundle\Form\EventListener\ChangePasswordSubscriber;
use Oro\Bundle\UserBundle\Acl\Manager as AclManager;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;

class ChangePasswordType extends AbstractType
{
    /**
     * @var AclManager
     */
    protected $aclManager;

    /**
     * @var SecurityContextInterface
     */
    protected $security;

    /**
     * @param AclManager $aclManager      ACL manager
     * @param SecurityContextInterface $security        Security context
     */
    public function __construct(AclManager $aclManager, SecurityContextInterface $security)
    {
        $this->aclManager = $aclManager;
        $this->security   = $security;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventSubscriber(
            new ChangePasswordSubscriber($builder->getFormFactory(), $this->aclManager, $this->security)
        );

        $builder->add(
            'currentPassword',
            'password',
            array(
                'required' => true,
                'label' => 'Current password',
                'constraints' => array(
                    new UserPassword()
                ),
                'mapped' => false,
            )
        )
        ->add(
            'plainPassword',
            'repeated',
            array(
                'required' => true,
                'type' => 'password',
                'invalid_message' => 'The password fields must match.',
                'options' => array(
                    'attr' => array(
                        'class' => 'password-field'
                    )
                ),
                'first_options'  => array('label' => 'New password'),
                'second_options' => array('label' => 'Repeat password'),
                'mapped' => false,
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_change_password';
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'inherit_data' => true,
            )
        );
    }
}
