<?php

namespace PimEnterprise\Bundle\SecurityBundle\Form\Type;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\AbstractType;
use Doctrine\ORM\EntityRepository;
use Oro\Bundle\UserBundle\Entity\User;

/**
 * Form type that provides a choice of available user roles
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class RolesType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'class'         => 'OroUserBundle:Role',
                'property'      => 'label',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('r')
                        ->where('r.role <> :anon')
                        ->setParameter('anon', User::ROLE_ANONYMOUS);
                },
                'multiple'      => true,
                'required'      => false,
                'select2'       => true,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'entity';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pimee_security_roles';
    }
}
