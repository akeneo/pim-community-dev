<?php

namespace PimEnterprise\Bundle\EnrichBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Pim\Bundle\EnrichBundle\Form\Type\AttributeGroupType as BaseAttributeGroupType;
use PimEnterprise\Bundle\EnrichBundle\Form\Subscriber\AttributeGroupRightsSubscriber;

/**
 * Form type for AttributeGroup with custom ACL configuration
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class AttributeGroupType extends BaseAttributeGroupType
{
    /**
     * @var AttributeGroupRightsSubscriber
     */
    protected $subscriber;

    /**
     * @param AttributeGroupRightsSubscriber $subscriber
     */
    public function __construct(AttributeGroupRightsSubscriber $subscriber)
    {
        $this->subscriber = $subscriber;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('rights', 'pimee_enrich_attribute_group_rights');

        $builder->addEventSubscriber($this->subscriber);
    }
}
