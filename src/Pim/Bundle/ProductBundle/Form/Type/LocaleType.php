<?php

namespace Pim\Bundle\ProductBundle\Form\Type;

use Pim\Bundle\ProductBundle\Entity\Repository\CurrencyRepository;
use Pim\Bundle\ProductBundle\Form\Subscriber\LocaleSubscriber;
use Pim\Bundle\ProductBundle\Helper\LocaleHelper;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;

/**
 * Type for locale form
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class LocaleType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('id', 'hidden');

        $builder->add('code', 'text', array('disabled' => true, 'required' => true));

        $this->addCurrencyField($builder);
    }

    /**
     * Add currency field
     *
     * @param FormBuilderInterface $builder
     *
     * @return null
     */
    protected function addCurrencyField(FormBuilderInterface $builder)
    {
        $builder->add(
            'defaultCurrency',
            'entity',
            array(
                'class'         => 'Pim\Bundle\ProductBundle\Entity\Currency',
                'property'      => 'code',
                'multiple'      => false,
                'query_builder' => function (CurrencyRepository $repository) {
                    return $repository->getActivatedCurrenciesQB();
                },
                'required'      => true,
                'label'         => 'Default currency (to display)'
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Pim\Bundle\ProductBundle\Entity\Locale'
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_product_locale';
    }
}
