<?php
namespace Oro\Bundle\TagBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Oro\Bundle\TagBundle\Entity\TagManager;

class TagAutocompleteType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'configs' => array(
                    'placeholder'    => 'oro.tag.form.choose_tag',
                    'extra_config'   => 'multi_autocomplete',
                    'multiple'       => true
                ),
                'autocomplete_alias' => 'tags',
            )
        );

        $resolver->setNormalizers(array());
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_tag_autocomplete';
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'oro_jqueryselect2_hidden';
    }
}
