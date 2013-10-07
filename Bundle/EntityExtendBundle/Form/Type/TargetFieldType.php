<?php

namespace Oro\Bundle\EntityExtendBundle\Form\Type;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;

use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityExtendBundle\Form\EventListener\TargetFieldSubscriber;

class TargetFieldType extends AbstractType
{
    /** @var  ConfigManager */
    protected $configManager;

    /** @var  Request */
    protected $request;

    /**
     * @param ConfigManager $configManager
     * @param Request $request
     */
    public function __construct(ConfigManager $configManager, Request $request)
    {
        $this->configManager = $configManager;
        $this->request       = $request;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventSubscriber(new TargetFieldSubscriber($this->request, $this->configManager));
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'choice';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_entity_target_field_type';
    }
}
