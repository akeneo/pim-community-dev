<?php
namespace Pim\Bundle\TranslationBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;

use Pim\Bundle\TranslationBundle\Form\Subscriber\AddTranslatedFieldSubscriber;

use Symfony\Component\DependencyInjection\ContainerInterface;

use Symfony\Component\Form\AbstractType;

/**
 * Translated field type for translation entities
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * TODO : Get activated locales from container service (must be create)
 */
class TranslatedFieldType extends AbstractType
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
        if (!class_exists($options['translation_class'])) {
            throw new \Exception('unable to find translation class');
        }

        if (!$options['field']) {
            throw new \Exception('must provide a field');
        }

        $subscriber = new AddTranslatedFieldSubscriber($builder->getFormFactory(), $this->container, $options);
        $builder->addEventSubscriber($subscriber);
    }

    /**
     * {@inheritdoc}
     *
     * - translation_class    : FQCN of the translation class
     * - entity_class         : FQCN of the based entity class
     * - locales              : Locales you wish to edit
     * - default_locale       : Name of the locale for the default translation
     * - required_locale      : Fields are required or not TODO : must be delete
     * - field                : Field name
     * - widget               : Widget used by translations fields
     */
    public function getDefaultOptions(array $options = array())
    {
        $options['translation_class'] = false;
        $options['entity_class'] = false;

        $options['locales'] = array('default', 'en_US', 'fr_FR');
        $options['default_locale'] = $this->container->getParameter('default_locale');
        $options['required_locale'] = array($this->container->getParameter('default_locale'));

        $options['field'] = 'name';
        $options['widget'] = 'text';

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_translatable_field';
    }
}
