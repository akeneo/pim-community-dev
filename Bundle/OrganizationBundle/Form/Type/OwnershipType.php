<?php

namespace Oro\Bundle\OrganizationBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Translation\TranslatorInterface;

class OwnershipType extends AbstractType
{
    const NAME = 'oro_type_choice_ownership_type';

    const OWNER_TYPE_NONE = 'NONE';
    const OWNER_TYPE_USER = 'USER';
    const OWNER_TYPE_BUSINESS_UNIT = 'BUSINESS_UNIT';
    const OWNER_TYPE_ORGANIZATION = 'ORGANIZATION';

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'choices' => $this->getOwnershipsArray()
            )
        );
    }

    public function getOwnershipsArray()
    {
        return  array(
            self::OWNERSHIP_TYPE_NONE => $this->translator->trans('None'),
            self::OWNERSHIP_TYPE_USER => $this->translator->trans('User'),
            self::OWNERSHIP_TYPE_BUSINESS_UNIT => $this->translator->trans('Business Unit'),
            self::OWNERSHIP_TYPE_ORGANIZATION => $this->translator->trans('Organization'),
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
