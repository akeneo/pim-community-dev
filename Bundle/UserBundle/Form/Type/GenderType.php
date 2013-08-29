<?php

namespace Oro\Bundle\UserBundle\Form\Type;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\AbstractType;

use Oro\Bundle\UserBundle\Provider\GenderProvider;

class GenderType extends AbstractType
{
    const NAME = 'oro_gender';

    /**
     * @var GenderProvider
     */
    protected $genderProvider;

    /**
     * @param GenderProvider $genderProvider
     */
    public function __construct(GenderProvider $genderProvider)
    {
        $this->genderProvider = $genderProvider;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * @return string
     */
    public function getParent()
    {
        return 'choice';
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'choices'     => $this->genderProvider->getChoices(),
                'multiple'    => false,
                'expanded'    => false,
                'empty_value' => 'oro.user.form.choose_gender'
            )
        );
    }
}
