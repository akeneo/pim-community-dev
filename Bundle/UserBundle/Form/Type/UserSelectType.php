<?php
namespace Oro\Bundle\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Oro\Bundle\FormBundle\EntityAutocomplete\Transformer\EntityTransformerInterface;

class UserSelectType extends AbstractType
{
    /**
     * @var EntityTransformerInterface
     */
    protected $transfrormer;

    /**
     * @param EntityTransformerInterface $transformer
     */
    public function __construct(EntityTransformerInterface $transformer)
    {
        $this->transformer = $transformer;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'configs' => array(
                    'datasource' => 'user',
                    'placeholder' => 'Choose a user...',
                    'route' => 'oro_user_autocomplete',
                    'properties' => array('firstName', 'lastName'),
                    'width' => '400px'
                ),
                'entity_class' => 'Oro\Bundle\UserBundle\Entity\User',
                'autocomplete_transformer' => $this->transformer,
                //'autocomplete_alias' => 'users'
            )
        );
    }

    public function getParent()
    {
        return 'oro_jqueryselect2_hidden';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_user_select';
    }
}
