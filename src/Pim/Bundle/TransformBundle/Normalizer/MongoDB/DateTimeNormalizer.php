<?php

namespace Pim\Bundle\TransformBundle\Normalizer\MongoDB;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

use \MongoDate;

/**
 * Normalize a DateTime to a MongoDate
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DateTimeNormalizer implements NormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return ($data instanceof \DateTime && ProductNormalizer::FORMAT === $format);
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($dateTime, $format = null, array $context = [])
    {
        return new MongoDate($dateTime->getTimeStamp());
    }
}
