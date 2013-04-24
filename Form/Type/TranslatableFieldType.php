<?php
namespace Pim\Bundle\TranslationBundle\Form\Type;

use Symfony\Component\Form\Exception\FormException;

use Symfony\Component\Form\FormBuilderInterface;

use Pim\Bundle\TranslationBundle\Form\Subscriber\AddTranslatableFieldSubscriber;

use Symfony\Component\DependencyInjection\ContainerInterface;

use Symfony\Component\Form\AbstractType;

/**
 * Translatable field type for translation entities
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class TranslatableFieldType extends AbstractType
{

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * Define constructor with container injection
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (!class_exists($options['entity_class'])) {
            throw new FormException('unable to find entity class');
        }

        if (!class_exists($options['translation_class'])) {
            throw new FormException('unable to find translation class');
        }

        if (!$options['field']) {
            throw new FormException('must provide a field');
        }

        $subscriber = new AddTranslatableFieldSubscriber($builder->getFormFactory(), $this->container, $options);
        $builder->addEventSubscriber($subscriber);
    }

    /**
     * {@inheritdoc}
     *
     * - translation_class    : FQCN of the translation class
     * - entity_class         : FQCN of the based entity class
     * - locales              : Locales you wish to edit
     * - default_locale       : Name of the locale for the default translation
     * - required_locale      : Fields are required or not
     * - field                : Field name
     * - widget               : Widget used by translations fields
     */
    public function getDefaultOptions(array $options = array())
    {
        $options['translation_class'] = false;
        $options['entity_class'] = false;
        $options['field'] = false;

        $options['locales'] = $this->getActiveLocales();
        $options['default_locale'] = $this->container->getParameter('default_locale');
        $options['required_locale'] = array($this->container->getParameter('default_locale'));

        $options['widget'] = 'text';

        return $options;
    }

    /**
     * Get active locales
     *
     * @return multitype:string
     */
    protected function getActiveLocales()
    {
        $locales = $this->container->get('pim_config.manager.locale')->getActiveCodes();
        array_unshift($locales, $this->container->getParameter('default_locale'));

        return $locales;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_translatable_field';
    }
}
