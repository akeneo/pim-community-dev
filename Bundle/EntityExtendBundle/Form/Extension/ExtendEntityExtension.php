<?php

namespace Oro\Bundle\EntityExtendBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;

use Oro\Bundle\EntityExtendBundle\Extend\ExtendManager;

class ExtendEntityExtension extends AbstractTypeExtension
{
    /**
     * @var ExtendManager
     */
    protected $extendManager;

    /**
     * @param ExtendManager $extendManager
     */
    public function __construct(ExtendManager $extendManager)
    {
        $this->extendManager = $extendManager;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $xm = $this->extendManager;

        $className = $options['data_class'];
        if ($className && $xm->getConfigProvider()->hasConfig($className)) {
            $builder->add(
                'additional',
                'custom_entity_type',
                array(
                    'inherit_data' => true,
                    'class_name' => $className
                )
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return 'form';
    }
}
