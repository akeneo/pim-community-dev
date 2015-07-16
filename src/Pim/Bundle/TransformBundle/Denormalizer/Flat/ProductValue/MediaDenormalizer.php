<?php

namespace Pim\Bundle\TransformBundle\Denormalizer\Flat\ProductValue;

use Pim\Bundle\CatalogBundle\Manager\MediaManager;

/**
 * Denormalize a product media
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * TODO: should be deleted
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

        $context = $context + ['use_relative_media_path' => false];

        return $this->manager->createFromFilename($data, $context['use_relative_media_path']);
    }
}
