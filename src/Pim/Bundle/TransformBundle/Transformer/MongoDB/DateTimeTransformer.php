<?php

namespace Pim\Bundle\TransformBundle\Transformer\MongoDB;

use Pim\Bundle\TransformBundle\Transformer\ObjectTransformerInterface;

use \MongoDate;

/**
 * Transform a DateTime to a MongoDate
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DateTimeTransformer implements ObjectTransformerInterface
{
    /**
     * {@inheritdoc}
     */
    public function transform($dateTime, array $context = [])
    {
        return new MongoDate($dateTime->getTimeStamp());
    }
}
