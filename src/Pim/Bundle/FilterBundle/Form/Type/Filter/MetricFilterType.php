<?php

namespace Pim\Bundle\FilterBundle\Form\Type\Filter;

use Monolog\Logger;

use Symfony\Component\Form\FormInterface;

use Symfony\Component\Form\FormView;

use Oro\Bundle\FilterBundle\Form\Type\Filter\FilterType;

use Symfony\Component\Form\AbstractType;

use Oro\Bundle\FormBundle\Form\Exception\FormException;

use Symfony\Component\Form\FormBuilderInterface;

use Oro\Bundle\MeasureBundle\Manager\MeasureManager;

use Symfony\Component\Translation\TranslatorInterface;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Oro\Bundle\FilterBundle\Form\Type\Filter\NumberFilterType;

/**
 * Metric filter type for products
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
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
     * {@inheritDoc}
     */
    public function getParent()
    {
        return FilterType::NAME;
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

        // FIXME
        $family = 'Weight';
//         $family = $options['field_options']['family'];
        $result['choices'] = $this->measureManager->getUnitSymbolsForFamily($family);

        return $result;
    }
}
