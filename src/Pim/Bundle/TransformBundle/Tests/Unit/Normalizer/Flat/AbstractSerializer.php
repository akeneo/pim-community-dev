<?php

namespace Pim\Bundle\TransformBundle\Tests\Unit\Normalizer\Flat;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Abstract Serializer class to get a class implementing both
 * SerializerInterace and NormalizerInterface (not doable with
 * phpUnit mocking framework)
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractSerializer implements SerializerInterface, NormalizerInterface
{
}
