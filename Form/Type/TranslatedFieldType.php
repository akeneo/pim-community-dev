<?php
namespace Pim\Bundle\TranslationBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;

use Pim\Bundle\TranslationBundle\Form\Subscriber\AddTranslatedFieldSubscriber;

use Symfony\Component\DependencyInjection\ContainerInterface;

use Symfony\Component\Form\AbstractType;

/**
 *
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class TranslatedFieldType extends AbstractType
{

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
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
        if (!class_exists($options['personal_translation']))
        {
            throw new \Exception('unable to find personal translation class');
        }

        if (!$options['field'])
        {
            throw new \Exception('must provide a field');
        }

        $subscriber = new AddTranslatedFieldSubscriber($builder->getFormFactory(), $this->container, $options);
        $builder->addEventSubscriber($subscriber);
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions(array $options = array())
    {
        $options['remove_empty'] = true; //Personal Translations without content are removed
        $options['csrf_protection'] = false;
        $options['personal_translation'] = false; //Personal Translation class

        // FIXME : must be injected
        $options['locales'] = array('default', 'en_US', 'fr_FR'); //the locales you wish to edit
        // FIXME : must be injected
        $options['required_locale'] = array('default'); //the required locales cannot be blank

        $options['field'] = 'name'; //the field that you wish to translate
        $options['widget'] = "text"; //change this to another widget like 'texarea' if needed
        $options['entity_manager_removal'] = false; //auto removes the Personal Translation thru entity manager

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
