<?php

namespace Pim\Bundle\TransformBundle\Normalizer\MongoDB;

use Akeneo\Bundle\MeasureBundle\Convert\MeasureConverter;
use Akeneo\Bundle\MeasureBundle\Manager\MeasureManager;
use Akeneo\Bundle\StorageUtilsBundle\MongoDB\MongoObjectsFactory;
use Pim\Bundle\CatalogBundle\Model\MetricInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalize a metric entity into an MongoDB Document
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MetricNormalizer implements NormalizerInterface
{
    /** @var MeasureConverter */
    protected $converter;

    /** @var MeasureManager */
    protected $manager;

    /** @var MongoObjectsFactory */
    protected $mongoFactory;

    /**
     * @param MongoObjectsFactory $mongoFactory
     * @param MeasureConverter    $converter
     * @param MeasureManager      $manager
     */
    public function __construct(
        MongoObjectsFactory $mongoFactory,
        MeasureConverter $converter,
        MeasureManager $manager
    ) {
        $this->mongoFactory = $mongoFactory;
        $this->converter    = $converter;
        $this->manager      = $manager;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return ($data instanceof MetricInterface && ProductNormalizer::FORMAT === $format);
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($metric, $format = null, array $context = [])
    {
        if (null === $metric->getData() || "" === $metric->getData() ||
            null === $metric->getUnit() || "" === $metric->getUnit()) {
            return null;
        }

        $this->createMetricBaseValues($metric);

        $data = [];
        $data['_id']      = $this->mongoFactory->createMongoId();
        $data['unit']     = $metric->getUnit();
        $data['data']     = $metric->getData();
        $data['baseUnit'] = $metric->getBaseUnit();
        $data['baseData'] = $metric->getBaseData();
        $data['family']   = $metric->getFamily();

        return $data;
    }

    /**
     * Convert data in standard unit for metrics
     *
     * @param MetricInterface $metric
     */
    protected function createMetricBaseValues(MetricInterface $metric)
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
