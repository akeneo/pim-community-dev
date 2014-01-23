<?php

namespace Pim\Bundle\FlexibleEntityBundle\Form\Type;

use Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceList;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Phone type
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
            [
                'empty_value'   => 'Choose phone type...',
                'empty_data'    => null,
                'choice_list'   => new ChoiceList(
                    array_keys(self::getTypesArray()),
                    array_values(self::getTypesArray())
                ),
                'attr' => ['class' => 'oro-multiselect']
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getTypesArray()
    {
        return [
            self::TYPE_OFFICE      => 'Office phone',
            self::TYPE_CELL        => 'Cell phone'
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_flexibleentity_phone';
    }
}
