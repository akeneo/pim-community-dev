<?php

namespace Pim\Bundle\UIBundle\Form\Type;

use Pim\Component\Localization\LocaleResolver;
use Pim\Bundle\LocalizationBundle\Form\DataTransformer\NumberLocalizerTransformer;
use Pim\Bundle\UIBundle\Form\Transformer\NumberTransformer;
use Pim\Component\Localization\Localizer\LocalizerInterface;
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

    /** @var Constraint */
    protected $numberConstraint;

    /**
     * @param LocalizerInterface $localizer
     * @param LocaleResolver     $localeResolver
     * @param Constraint         $numberConstraint
     */
    public function __construct(
        LocalizerInterface $localizer,
        LocaleResolver $localeResolver,
        Constraint $numberConstraint
    ) {
        $this->localizer        = $localizer;
        $this->localeResolver   = $localeResolver;
        $this->numberConstraint = $numberConstraint;
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
        $localeOptions = $this->localeResolver->getFormats();

        $resolver->setDefaults(
            [
                'decimals_allowed'           => true,
                'invalid_message'            => $this->numberConstraint->message,
                'invalid_message_parameters' => ['{{ decimal_separator }}' => $localeOptions['decimal_separator']],
                'locale_options'             => $localeOptions
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
