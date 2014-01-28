<?php

namespace Pim\Bundle\TranslationBundle\Form\Type;

use Symfony\Component\Form\Exception\InvalidConfigurationException;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Validator\ValidatorInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Pim\Bundle\UserBundle\Context\UserContext;
use Pim\Bundle\TranslationBundle\Form\Subscriber\AddTranslatableFieldSubscriber;
use Pim\Bundle\CatalogBundle\Helper\LocaleHelper;

/**
 * Translatable field type for translation entities
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TranslatableFieldType extends AbstractType
{
    /**
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * @var UserContext
     */
    protected $userContext;

    /**
     * @var LocaleHelper
     */
    protected $localeHelper;

    /**
     * @param ValidatorInterface $validator
     * @param UserContext        $userContext
     * @param LocaleHelper       $localeHelper
     */
    public function __construct(ValidatorInterface $validator, UserContext $userContext, LocaleHelper $localeHelper)
    {
        $this->validator    = $validator;
        $this->userContext  = $userContext;
        $this->localeHelper = $localeHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // TODO : use resolver to do that, see http://symfony.com/doc/current/components/options_resolver.html
        if (!class_exists($options['entity_class'])) {
            throw new InvalidConfigurationException('unable to find entity class');
        }

        if (!class_exists($options['translation_class'])) {
            throw new InvalidConfigurationException('unable to find translation class');
        }

        if (!$options['field']) {
            throw new InvalidConfigurationException('must provide a field');
        }

        if (!is_array($options['required_locale'])) {
            throw new InvalidConfigurationException('required locale(s) must be an array');
        }

        $subscriber = new AddTranslatableFieldSubscriber(
            $builder->getFormFactory(),
            $this->validator,
            $this->userContext,
            $this->localeHelper,
            $options
        );
        $builder->addEventSubscriber($subscriber);
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);
        $resolver->setRequired(array('entity_class', 'translation_class', 'field', 'required_locale'));
        $resolver->setDefaults(
            array(
                'translation_class' => false,
                'entity_class' => false,
                'field' => false,
                'locales' => $this->userContext->getUserCodes(),
                'required_locale' => array(),
                'widget' => 'text'
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_translatable_field';
    }
}
