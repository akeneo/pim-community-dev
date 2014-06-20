<?php

namespace PimEnterprise\Bundle\UserBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Pim\Bundle\UserBundle\Form\Type\AclRoleType as PimAclRoleType;
use PimEnterprise\Bundle\UserBundle\Form\Subscriber\CategoryOwnershipSubscriber;

/**
 * Overriden AclRoleType to add product ownership rights per category
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class AclRoleType extends PimAclRoleType
{
    /**
     * @var CategoryOwnershipSubscriber
     */
    protected $subscriber;

    /**
     * @param array                       $privilegeTypeConfig
     * @param CategoryOwnershipSubscriber $subscriber
     */
    public function __construct(array $privilegeTypeConfig, CategoryOwnershipSubscriber $subscriber)
    {
        parent::__construct($privilegeTypeConfig);

        $this->subscriber = $subscriber;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->addEventSubscriber($this->subscriber);
    }
}
