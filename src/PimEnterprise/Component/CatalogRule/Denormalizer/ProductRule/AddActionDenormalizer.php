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

use PimEnterprise\Component\CatalogRule\Model\ProductAddActionInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * Denormalize product add rule actions.
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class AddActionDenormalizer implements DenormalizerInterface
{
    /** @var string */
    protected $addActionClass;

    /**
     * @param string $addActionClass should implement
     *     \PimEnterprise\Component\CatalogRule\Model\ProductAddActionInterface
     */
    public function __construct($addActionClass)
    {
        $this->addActionClass = $addActionClass;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        return new $this->addActionClass($data);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return isset($data['type']) && ProductAddActionInterface::ACTION_TYPE === $data['type'];
    }
}
