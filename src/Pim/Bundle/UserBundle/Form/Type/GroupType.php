<?php

namespace Pim\Bundle\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class GroupType
 *
 * @author    Clement Gautier <clement.gautier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', [
                'required' => true,
            ])
            ->add('appendUsers', 'oro_entity_identifier', [
                'class'    => 'PimUserBundle:User',
                'required' => false,
                'mapped'   => false,
                'multiple' => true,
            ])
            ->add('removeUsers', 'oro_entity_identifier', [
                'class'    => 'PimUserBundle:User',
                'required' => false,
                'mapped'   => false,
                'multiple' => true,
            ]);

        $builder->get('appendUsers')->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $users = $event->getData();
            $group = $event->getForm()->getParent()->getData();

            foreach ($users as $user) {
                $user->addGroup($group);
            }
        });

        $builder->get('removeUsers')->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $users = $event->getData();
            $group = $event->getForm()->getParent()->getData();

            foreach ($users as $user) {
                $user->removeGroup($group);
            }
        });
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Pim\Bundle\UserBundle\Entity\Group',
            'intention'  => 'group',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_user_group';
    }
}
