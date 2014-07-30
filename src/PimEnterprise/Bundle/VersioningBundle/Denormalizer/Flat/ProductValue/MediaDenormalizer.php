<?php

namespace PimEnterprise\Bundle\VersioningBundle\Denormalizer\Flat\ProductValue;

use PimEnterprise\Bundle\CatalogBundle\Manager\MediaManager;

/**
 * Denormalize a product media
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
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
