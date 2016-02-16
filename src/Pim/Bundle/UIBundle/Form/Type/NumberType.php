<?php

namespace Pim\Bundle\UIBundle\Form\Type;

use Pim\Bundle\LocalizationBundle\Form\DataTransformer\NumberLocalizerTransformer;
use Pim\Bundle\UIBundle\Form\Transformer\NumberTransformer;
use Pim\Component\Localization\LocaleResolver;
use Pim\Component\Localization\Localizer\LocalizerInterface;
use Pim\Component\Localization\Validator\Constraints\NumberFormat;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraint;

/**
 * PIM number type
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NumberType extends AbstractType
{
    /** @var LocalizerInterface */
    protected $localizer;

    /** @var LocaleResolver */
    protected $localeResolver;

    /** @var array */
    protected $decimalSeparators;

    /**
     * @param LocalizerInterface $localizer
     * @param LocaleResolver     $localeResolver
     * @param array              $decimalSeparators
     */
    public function __construct(
        LocalizerInterface $localizer,
        LocaleResolver $localeResolver,
        array $decimalSeparators
    ) {
        $this->localizer         = $localizer;
        $this->localeResolver    = $localeResolver;
        $this->decimalSeparators = $decimalSeparators;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addViewTransformer(new NumberTransformer());
        $builder->addModelTransformer(new NumberLocalizerTransformer($this->localizer, $options['locale_options']));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $options = ['locale' => $this->localeResolver->getCurrentLocale()];
        $decimalSeparator = $this->localeResolver->getFormats()['decimal_separator'];

        $constraint = new NumberFormat($this->decimalSeparators);
        $constraint->decimalSeparator = $decimalSeparator;

        $resolver->setDefaults(
            [
                'decimals_allowed'           => true,
                'locale_options'             => $options,
                'invalid_message'            => $constraint->getMessageKey(),
                'invalid_message_parameters' => $constraint->getMessageParams(),
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_number';
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'text';
    }
}
