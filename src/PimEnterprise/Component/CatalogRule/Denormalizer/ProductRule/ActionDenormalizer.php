<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\CatalogRule\Denormalizer\ProductRule;

use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * Denormalize product rule actions.
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class ActionDenormalizer implements DenormalizerInterface
{
    /** @var string */
    protected $actionClass;

    /** @var string */
    protected $actionType;

    /**
     * @param string $actionClass should implement
     *     \PimEnterprise\Component\CatalogRule\Model\ActionInterface
     * @param string $actiontype
     */
    public function __construct($actionClass, $actionType)
    {
        $this->actionClass = $actionClass;
        $this->actionType  = $actionType;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        return new $this->actionClass($data);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return isset($data['type']) && $this->actionType === $data['type'];
    }
}
