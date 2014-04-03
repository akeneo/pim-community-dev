<?php

namespace Pim\Bundle\UserBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Doctrine\ORM\EntityRepository;
use Oro\Bundle\UserBundle\Form\Type\UserType as OroUserType;
use Oro\Bundle\UserBundle\Form\EventListener\UserSubscriber;
use Oro\Bundle\UserBundle\Entity\User;

use Pim\Bundle\UserBundle\Form\Subscriber\UserPreferencesSubscriber;

/**
 * Overriden user form to add a custom subscriber
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UserType extends OroUserType
{
    /** @var UserPreferencesSubscriber */
    protected $subscriber;

    /**
     * @param SecurityContextInterface  $security
     * @param Request                   $request
     * @param UserPreferencesSubscriber $subscriber
     */
    public function __construct(
        SecurityContextInterface $security,
        Request $request,
        UserPreferencesSubscriber $subscriber
    ) {
        parent::__construct($security, $request);

        $this->subscriber = $subscriber;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->addEventSubscriber($this->subscriber);
    }

    /**
     * {@inheritdoc}
     */
    public function addEntityFields(FormBuilderInterface $builder)
    {
        $builder->addEventSubscriber(
            new UserSubscriber($builder->getFormFactory(), $this->security)
        );
        $this->setDefaultUserFields($builder);
        $builder
            ->add(
                'rolesCollection',
                'entity',
                array(
                    'label'          => 'Roles',
                    'class'          => 'OroUserBundle:Role',
                    'property'       => 'label',
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('r')
                            ->where('r.role <> :anon')
                            ->setParameter('anon', User::ROLE_ANONYMOUS);
                    },
                    'multiple'       => true,
                    'expanded'       => true,
                    'required'       => !$this->isMyProfilePage,
                    'read_only'      => $this->isMyProfilePage,
                    'disabled'      => $this->isMyProfilePage,
                )
            )
            ->add(
                'groups',
                'entity',
                array(
                    'class'          => 'OroUserBundle:Group',
                    'property'       => 'name',
                    'multiple'       => true,
                    'expanded'       => true,
                    'required'       => false,
                    'read_only'      => $this->isMyProfilePage,
                    'disabled'       => $this->isMyProfilePage
                )
            )
            ->add(
                'plainPassword',
                'repeated',
                array(
                    'type'           => 'password',
                    'required'       => true,
                    'first_options'  => array('label' => 'Password'),
                    'second_options' => array('label' => 'Re-enter password'),
                )
            )
            ->add(
                'emails',
                'collection',
                array(
                    'type'           => 'oro_user_email',
                    'allow_add'      => true,
                    'allow_delete'   => true,
                    'by_reference'   => false,
                    'prototype'      => true,
                    'prototype_name' => 'tag__name__',
                    'label'          => ' '
                )
            )
            ->add('imapConfiguration', 'oro_imap_configuration')
            ->add(
                'change_password',
                'oro_change_password'
            );
    }
}
