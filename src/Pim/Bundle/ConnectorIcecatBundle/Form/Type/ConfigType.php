<?php
namespace Pim\Bundle\ConnectorIcecatBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
/**
 * Config form type
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ConfigType extends AbstractType
{
    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('id', 'hidden');
        $builder->add('code', 'text', array('read_only' => true));
        $builder->add('value', 'text', array('attr' => array('style' => 'width:500px')));
    }

    /**
     * {@inheritDoc}
     */
    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class' => 'Pim\Bundle\ConnectorIcecatBundle\Entity\Config',
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'config';
    }
}
