<?php

namespace PimEnterprise\Bundle\EnrichBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;

use Pim\Bundle\EnrichBundle\Form\Subscriber\DisableFieldSubscriber;

/**
 * Form type for Locale
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class LocaleType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('code');
        $builder->add('permissions', 'pimee_enrich_locale_permissions');
        $this->addEventSubscribers($builder);
    }

    /**
     * Add event subscriber to channel form type
     * @param FormBuilderInterface $builder
     *
     * @return ChannelType
     */
    protected function addEventSubscribers(FormBuilderInterface $builder)
    {
        $builder->addEventSubscriber(new DisableFieldSubscriber('code'));

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pimee_enrich_locale';
    }
}
