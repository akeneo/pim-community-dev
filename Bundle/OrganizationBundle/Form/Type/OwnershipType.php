<?php

namespace Oro\Bundle\OrganizationBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Translation\TranslatorInterface;

class OwnershipType extends AbstractType
{
    const NAME = 'oro_type_choice_ownership_type';

    const OWNERSHIP_TYPE_USER = 1;
    const OWNERSHIP_TYPE_BUSINESS_UNIT = 2;
    const OWNERSHIP_TYPE_ORGANIZATION = 3;

    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $choices = array();
        foreach (self::getOwnershipsArray() as $key => $choice) {
            $choices[$key] = $this->translator->trans($choice);
        }
        $resolver->setDefaults(
            array(
                'choices' => $choices
            )
        );
    }

    public static function getOwnershipsArray()
    {
        return  array(
            self::OWNERSHIP_TYPE_USER => 'User',
            self::OWNERSHIP_TYPE_BUSINESS_UNIT => 'Business Unit',
            self::OWNERSHIP_TYPE_ORGANIZATION => 'Organization',
        );
    }

    public function getParent()
    {
        return 'choice';
    }

    public function getName()
    {
        return self::NAME;
    }
}