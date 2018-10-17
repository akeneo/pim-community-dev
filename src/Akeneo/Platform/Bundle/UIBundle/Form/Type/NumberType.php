<?php

namespace Akeneo\Platform\Bundle\UIBundle\Form\Type;

use Akeneo\Platform\Bundle\UIBundle\Form\Transformer\NumberLocalizerTransformer;
use Akeneo\Platform\Bundle\UIBundle\Resolver\LocaleResolver;
use Akeneo\Tool\Component\Localization\Factory\NumberFactory;
use Akeneo\Tool\Component\Localization\Localizer\LocalizerInterface;
use Akeneo\Tool\Component\Localization\Validator\Constraints\NumberFormat;
use Akeneo\Tool\Component\Localization\Validator\Constraints\NumberFormatValidator;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

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

    /** @var NumberFormatValidator */
    protected $formatValidator;

    /** @var NumberFactory */
    protected $numberFactory;

    /**
     * @param LocalizerInterface $localizer
     * @param LocaleResolver $localeResolver
     * @param NumberFormatValidator $formatValidator
     * @param NumberFactory $numberFactory
     */
    public function __construct(
        LocalizerInterface $localizer,
        LocaleResolver $localeResolver,
        NumberFormatValidator $formatValidator,
        NumberFactory $numberFactory
    ) {
        $this->localizer = $localizer;
        $this->localeResolver = $localeResolver;
        $this->formatValidator = $formatValidator;
        $this->numberFactory = $numberFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer(new NumberLocalizerTransformer($this->localizer, $options['locale_options']));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $options = ['locale' => $this->localeResolver->getCurrentLocale()];
        $decimalSeparator = $this->numberFactory->create($options)
            ->getSymbol(\NumberFormatter::DECIMAL_SEPARATOR_SYMBOL);

        $constraint = new NumberFormat();
        $constraint->decimalSeparator = $decimalSeparator;
        $message = $this->formatValidator->getMessage($constraint);

        $resolver->setDefaults(
            [
                'decimals_allowed'           => true,
                'invalid_message'            => $message,
                'invalid_message_parameters' => ['{{ decimal_separator }}' => $decimalSeparator],
                'locale_options'             => $options
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'pim_number';
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return TextType::class;
    }
}
