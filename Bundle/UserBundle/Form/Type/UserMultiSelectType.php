<?php
namespace Oro\Bundle\UserBundle\Form\Type;

use Doctrine\ORM\EntityManager;

use Oro\Bundle\NotificationBundle\Form\DataTransformer\StringToIdsTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UserMultiSelectType extends UserSelectType
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addViewTransformer(new StringToIdsTransformer($this->entityManager, 'OroUserBundle:User'));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'configs'             => array(
                    'width'                      => '400px',
                    'placeholder'                => 'oro.user.form.choose_user',
                    'result_template_twig'       => 'OroUserBundle:Js:userResult.html.twig',
                    'selection_template_twig'    => 'OroUserBundle:Js:userSelection.html.twig',
                    'extra_config'               => 'users_multiselect'
                ),
                'autocomplete_alias'   => 'users'
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_user_multiselect';
    }
}
