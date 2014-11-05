<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\VersioningBundle\Denormalizer\Flat\ProductValue;

use PimEnterprise\Bundle\CatalogBundle\Manager\MediaManager;

/**
 * Denormalize a product media
 *
 * @author Gildas Quemener <gildas@akeneo.com>
 */
class MediaDenormalizer extends AbstractValueDenormalizer
{
    /** @var MediaManager */
    protected $manager;

    /**
     * @param array        $supportedTypes
     * @param MediaManager $manager
     */
    public function __construct(
        array $supportedTypes,
        MediaManager $manager
    ) {
        parent::__construct($supportedTypes);

        $this->manager = $manager;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        if ($data === null || $data === '') {
            return null;
        }

        return $this->manager->createFromFilename($data);
    }
}
