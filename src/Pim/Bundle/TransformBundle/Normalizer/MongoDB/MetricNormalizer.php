<?php

namespace Pim\Bundle\TransformBundle\Normalizer\MongoDB;

use Pim\Bundle\CatalogBundle\Model\Metric;

use Akeneo\Bundle\MeasureBundle\Convert\MeasureConverter;
use Akeneo\Bundle\MeasureBundle\Manager\MeasureManager;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

use MongoId;

/**
 * Normalize a metric entity into an MongoDB Document
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MetricNormalizer implements NormalizerInterface
{
    /**
     * @var MeasureConverter
     */
    protected $converter;

    /**
     * @var MeasureManager
     */
    protected $manager;

    /**
     * Constructor
     *
     * @param MeasureConverter $converter the measure converter
     * @param MeasureManager   $manager   the measure manager
     */
    public function __construct(MeasureConverter $converter, MeasureManager $manager)
    {
        $this->converter = $converter;
        $this->manager   = $manager;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return ($data instanceof ProductPrice && ProductNormalizer::FORMAT === $format);
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($metric, $format = null, array $context = [])
    {
        $data = [];
        $data['_id'] = new MongoId;
        $data['unit'] = $metric->getUnit();
        $data['data'] = $metric->getData();

        $this->createMetricBaseValues($metric);

        $data['baseUnit'] = $metric->getBaseUnit();
        $data['baseData'] = $metric->getBaseData();

        $doc->family = $metric->getFamily();

        return $doc;
    }

    /**
     * Convert data in standard unit for metrics
     *
     * @param Metric
     */
    protected function createMetricBaseValues(Metric $metric)
    {
        $baseUnit = $this->manager->getStandardUnitForFamily($metric->getFamily());
        if (is_numeric($metric->getData())) {
            $baseData = $this->converter
                ->setFamily($metric->getFamily())
                ->convertBaseToStandard($metric->getUnit(), $metric->getData());
        } else {
            $baseData = null;
        }

        $metric->setBaseData($baseData);
        $metric->setBaseUnit($baseUnit);
    }

}
