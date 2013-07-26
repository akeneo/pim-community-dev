<?php

namespace Oro\Bundle\EmailBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class EmailTemplateTranslationType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'translatable_class'   => 'Oro\\Bundle\\EmailBundle\\Entity\\EmailTemplate',
                'intention'            => 'emailtemplate_translation',
                'extra_fields_message' => 'This form should not contain extra fields: "{{ extra_fields }}"',
                'cascade_validation'   => true,
            )
        );
    }

    public function getParent()
    {
        return 'a2lix_translations_gedmo';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_email_emailtemplate_translatation';
    }
}
