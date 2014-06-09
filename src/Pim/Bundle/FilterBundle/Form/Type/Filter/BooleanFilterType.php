<?php
namespace Pim\Bundle\FilterBundle\Form\Type\Filter;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Oro\Bundle\FilterBundle\Form\Type\Filter\AbstractChoiceType;
use Oro\Bundle\FilterBundle\Form\Type\Filter\ChoiceFilterType;

/**
 * Boolean filter type
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BooleanFilterType extends AbstractChoiceType
{
    /** @staticvar integer */
    const TYPE_YES = 1;
    const TYPE_NO = 0;

    /** @staticvar string */
    const NAME = 'pim_type_boolean_filter';

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return ChoiceFilterType::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $fieldChoices = array(
            self::TYPE_YES => $this->translator->trans('oro.filter.form.label_type_yes'),
            self::TYPE_NO => $this->translator->trans('oro.filter.form.label_type_no'),
        );

        $resolver->setDefaults(
            array(
                'field_options' => array('choices' => $fieldChoices),
            )
        );
    }
}
