<?php

namespace Pim\Bundle\FilterBundle\Form\Type\Filter;

use Oro\Bundle\FormBundle\Form\Exception\FormException;

use Symfony\Component\Form\FormBuilderInterface;

use Oro\Bundle\MeasureBundle\Manager\MeasureManager;

use Symfony\Component\Translation\TranslatorInterface;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Oro\Bundle\FilterBundle\Form\Type\Filter\NumberFilterType;

class MetricFilterType extends NumberFilterType
{
    /**
     * @staticvar string
     */
    const NAME = 'pim_type_metric_filter';

    /**
     * Constructor
     *
     * @param TranslatorInterface $translator
     * @param MeasureManager      $measureManager
     */
    public function __construct(TranslatorInterface $translator, MeasureManager $measureManager)
    {
        parent::__construct($translator);

        $this->measureManager = $measureManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return NumberFilterType::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('unit', 'choice', $this->createUnitOptions($options));
    }

    /**
     * Create unit symbols options list
     *
     * @param array $options
     *
     * @throws FormException
     *
     * @return array
     */
    protected function createUnitOptions(array $options)
    {
        $result = array('required' => true);
        if (!$options['family']) {
            throw new FormException('Family option must be set');
        }

        $result['choices'] = $this->measureManager->getUnitSymbolsForFamily($options['family']);

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);

        $resolver->replaceDefaults(array('data_type' => self::DATA_DECIMAL));
        $resolver->setDefaults(
            array(
                'family' => null
            )
        );
    }
}
