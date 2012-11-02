<?php
namespace Akeneo\CatalogBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Akeneo\CatalogBundle\Model\BaseFieldFactory;

/**
 * Type for channel form (independant of concrete impl)
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ChannelType extends AbstractType
{

    /**
     * @var string
     */
    protected $channelClass;

    /**
     * @var string
     */
    protected $channelLocaleClass;

    /**
     * Construct with full name of concrete impl of channel and locale classes
     * @param string $channelClass
     * @param string $channelLocaleClass
     */
    public function __construct($channelClass, $channelLocaleClass)
    {
        $this->channelClass = $channelClass;
        $this->channelLocaleClass = $channelLocaleClass;
    }

    /**
     * Build form
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $entity = $options['data'];

        // TODO drive from type and not add if in twig template ?
        $builder->add('id', 'hidden');

        $builder->add(
            'code', 'text', array(
                'disabled'  => ($entity->getId())? true : false
            )
        );

        $builder->add(
            'isDefault', 'checkbox', array('label' => 'Is default', 'required' => false)
        );

        /*
        // TODO:provides exhaustive or configured list
        $localeOptions = array('fr_FR' => 'fr_FR', 'en_US' => 'en_US');

        $builder->add(
            'defaultLocale', 'choice', array(
                'choices'   => $localeOptions,
                'required'  => true,
                'label'     => 'Locale'
            )
        );*/

        $builder->add(
            'locales', 'collection', array(
                'type' => new ChannelLocaleType($this->channelLocaleClass),
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
            )
        );
    }

    /**
     * Setup default options
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => $this->channelClass
            )
        );
    }

    /**
     * Get identifier
     * @return string
     */
    public function getName()
    {
        return 'akeneo_catalogbundle_channel';
    }
}
