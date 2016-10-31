<?php

namespace Pim\Bundle\EnrichBundle\Form\Type;

use Akeneo\Bundle\MeasureBundle\Manager\MeasureManager;
use Doctrine\ORM\EntityManager;
use Pim\Component\Catalog\AttributeTypes;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

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
        $this->entityManager = $entityManager;
        $this->attributeClass = $attributeClass;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $metricAttributes = $this->entityManager
            ->getRepository($this->attributeClass)
            ->findBy(['attributeType' => AttributeTypes::METRIC]);

        foreach ($metricAttributes as $attribute) {
            if ($units = $this->measureManager->getUnitSymbolsForFamily($attribute->getMetricFamily())) {
                $builder->add(
                    $attribute->getCode(),
                    'choice',
                    [
                        'choices'                   => array_combine(array_keys($units), array_keys($units)),
                        'empty_value'               => 'Do not convert',
                        'required'                  => false,
                        'select2'                   => true,
                        'label'                     => $attribute->getLabel(),
                        'choice_translation_domain' => 'measures'
                    ]
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
