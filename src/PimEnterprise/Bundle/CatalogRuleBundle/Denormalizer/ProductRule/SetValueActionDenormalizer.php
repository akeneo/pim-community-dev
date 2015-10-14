<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\CatalogRuleBundle\Denormalizer\ProductRule;

use PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductSetValueActionInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * Denormalize product set value rule actions.
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
class SetValueActionDenormalizer implements DenormalizerInterface
{
    /** @var string */
    protected $setValueActionClass;

    /**
     * @param string $setValueActionClass should implement
     *                                    \PimEnterprise\Bundle\CatalogRuleBundle\Model
     *                                    \ProductSetValueActionInterface
     */
    public function __construct($setValueActionClass)
    {
        $this->setValueActionClass = $setValueActionClass;
    }
    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        return new $this->setValueActionClass($data);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return isset($data['type']) && ProductSetValueActionInterface::ACTION_TYPE === $data['type'];
    }
}
