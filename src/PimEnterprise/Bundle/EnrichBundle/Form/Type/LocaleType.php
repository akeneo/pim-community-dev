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
        $builder->add('code', 'text', ['disabled' => true]);
        $builder->add('permissions', 'pimee_enrich_locale_permissions');
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pimee_enrich_locale';
    }
}
