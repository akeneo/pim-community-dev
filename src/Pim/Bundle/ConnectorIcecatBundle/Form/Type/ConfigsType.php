<?php
namespace Pim\Bundle\ConnectorIcecatBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
/**
 * Configs form type
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ConfigsType extends AbstractType
{
    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('configs', 'collection', array(
                'type' => new ConfigType(),
                'allow_add' => false,
                'allow_delete' => false,
                'by_reference' => false,
            )
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class' => 'Pim\Bundle\ConnectorIcecatBundle\Entity\Configs'
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'configs';
    }
}
