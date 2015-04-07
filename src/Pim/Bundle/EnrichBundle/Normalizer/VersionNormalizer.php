<?php

namespace Pim\Bundle\EnrichBundle\Normalizer;

use Pim\Bundle\VersioningBundle\Model\Version;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Version normalizer
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VersionNormalizer implements NormalizerInterface
{
    /** @var array */
    protected $supportedFormat = ['array', 'json'];

    /**
     * {@inheritdoc}
     */
    public function normalize($version, $format = null, array $context = array())
    {
        return [
            'id'           => $version->getId(),
            'author'       => $version->getAuthor(),
            'resource_id'  => (string) $version->getResourceId(),
            'snapshot'     => $version->getSnapshot(),
            'changeset'    => $version->getChangeset(),
            'context'      => $version->getContext(),
            'version'      => $version->getVersion(),
            'logged_at'    => $version->getLoggedAt()->format('Y-m-d H:i:s'),
            'pending'      => $version->isPending()
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Version && in_array($format, $this->supportedFormat);
    }
}
