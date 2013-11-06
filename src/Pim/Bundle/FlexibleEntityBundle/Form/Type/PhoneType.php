<?php

namespace Pim\Bundle\FlexibleEntityBundle\Form\Type;

use Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceList;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Phone type
 */
class PhoneType extends CollectionItemAbstract
{
    const TYPE_OFFICE = 1;
    const TYPE_CELL = 2;

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('id', 'hidden');
        $builder->add('data', 'text');
        $builder->add(
            'type',
            'choice',
            array(
                'empty_value'   => 'Choose phone type...',
                'empty_data'    => null,
                'choice_list'   => new ChoiceList(
                    array_keys(self::getTypesArray()),
                    array_values(self::getTypesArray())
                ),
                'attr' => array ('class' => 'oro-multiselect')
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getTypesArray()
    {
        return array(
            self::TYPE_OFFICE      => 'Office phone',
            self::TYPE_CELL        => 'Cell phone'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_flexibleentity_phone';
    }
}
