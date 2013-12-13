<?php

namespace Pim\Bundle\UserBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
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
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->addEventSubscriber(new UserPreferencesSubscriber());
    }
}
