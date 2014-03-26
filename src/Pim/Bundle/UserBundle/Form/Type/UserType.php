<?php

namespace Pim\Bundle\UserBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Oro\Bundle\UserBundle\Form\Type\UserType as OroUserType;
use Pim\Bundle\UserBundle\Form\Subscriber\UserPreferencesSubscriber;

/**
 * Overriden user form to add a custom subscriber
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UserType extends OroUserType
{
    /** @var UserPreferencesSubscriber */
    protected $subscriber;

    /**
     * @param SecurityContextInterface  $security
     * @param Request                   $request
     * @param UserPreferencesSubscriber $subscriber
     */
    public function __construct(
        SecurityContextInterface $security,
        Request $request,
        UserPreferencesSubscriber $subscriber
    ) {
        parent::__construct($security, $request);

        $this->subscriber = $subscriber;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->addEventSubscriber($this->subscriber);
    }
}
