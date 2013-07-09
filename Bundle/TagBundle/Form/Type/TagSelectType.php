<?php
namespace Oro\Bundle\TagBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TagSelectType extends AbstractType
{
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'configs' => array(
                    'placeholder'  => 'oro.tag.form.choose_tag',
                    'multiple'     => true,
                    'tokenSeparators' => array(',', ' '),
                    'tags' => true,
                    'extra_config' => 'multi_autocomplete',
                ),
                'autocomplete_alias' => 'tags',
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
        return 'oro_tag_select';
    }
}
