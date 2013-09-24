<?php

namespace Oro\Bundle\InstallerBundle\Form\Type\Configuration;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class WebsocketType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'oro_installer_websocket_host',
                'text',
                array(
                    'label'         => 'form.configuration.websocket.host',
                    'required'      => false,
                )
            )
            ->add(
                'oro_installer_websocket_port',
                'integer',
                array(
                    'label'         => 'form.configuration.websocket.port',
                    'required'      => false,
                    'constraints'   => array(
                        new Assert\Type(array('type' => 'integer')),
                    ),
                )
            );
    }

    public function getName()
    {
        return 'oro_installer_configuration_websocket';
    }
}
