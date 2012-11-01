<?php
namespace Akeneo\CatalogBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Akeneo\CatalogBundle\Model\BaseFieldFactory;

/**
 * Type for channel locale form
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ChannelLocaleType extends AbstractType
{

    /**
     * @var string
     */
    protected $channelLocaleClass;

    /**
     * Construct with full name of concrete impl of channel locale class
     * @param string $channelLocaleClass
     */
    public function __construct($channelLocaleClass)
    {
        $this->channelLocaleClass = $channelLocaleClass;
    }

    /**
     * Build form
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // TODO:provides exhaustive or configured list
        $localeOptions = array('fr_FR' => 'fr_FR', 'en_US' => 'en_US');

        $builder->add(
            'code', 'choice', array(
                'choices'   => $localeOptions,
                'required'  => true,
                'label'     => 'Locale'
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
                'data_class' => $this->channelLocaleClass,
            )
        );
    }

    /**
     * Get identifier
     * @return string
     */
    public function getName()
    {
        return 'akeneo_catalogbundle_channel_locale';
    }
}
