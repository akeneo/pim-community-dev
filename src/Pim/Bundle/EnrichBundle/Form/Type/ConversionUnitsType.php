<?php

namespace Pim\Bundle\EnrichBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Akeneo\Bundle\MeasureBundle\Manager\MeasureManager;
use Doctrine\ORM\EntityManager;

/**
 * Conversion units form type
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConversionUnitsType extends AbstractType
{
    /** @var MeasureManager */
    protected $measureManager;

    /**
     * @var string
     */
    protected $attributeClass;

    /**
     * Constructor
     *
     * @param MeasureManager $measureManager
     * @param EntityManager  $entityManager
     * @param string         $attributeClass
     */
    public function __construct(MeasureManager $measureManager, EntityManager $entityManager, $attributeClass)
    {
        $this->measureManager = $measureManager;
        $this->entityManager  = $entityManager;
        $this->attributeClass = $attributeClass;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $metricAttributes = $this->entityManager
            ->getRepository($this->attributeClass)
            ->findBy(array('attributeType' => 'pim_catalog_metric'));

        foreach ($metricAttributes as $attribute) {
            if ($units = $this->measureManager->getUnitSymbolsForFamily($attribute->getMetricFamily())) {
                $builder->add(
                    $attribute->getCode(),
                    'choice',
                    array(
                        'choices'     => array_combine(array_keys($units), array_keys($units)),
                        'empty_value' => 'Do not convert',
                        'required'    => false,
                        'select2'     => true,
                        'label'       => $attribute->getLabel()
                    )
                );
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_enrich_conversion_units';
    }
}
