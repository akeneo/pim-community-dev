<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\RuleEngine\Component\Denormalizer\ProductRule;

use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * Denormalize product rule conditions.
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class ConditionDenormalizer implements DenormalizerInterface
{
    /** @var string */
    protected $conditionClass;

    /**
     * @param string $conditionClass should implement
     *                               \Akeneo\Pim\Automation\RuleEngine\Component\Model\ProductConditionInterface
     */
    public function __construct($conditionClass)
    {
        $this->conditionClass = $conditionClass;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        return new $this->conditionClass($data);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null): bool
    {
        return $type === $this->conditionClass;
    }
}
