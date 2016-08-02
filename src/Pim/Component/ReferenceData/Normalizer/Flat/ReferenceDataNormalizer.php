<?php

namespace Pim\Component\ReferenceData\Normalizer\Flat;

use Pim\Bundle\VersioningBundle\Normalizer\Flat\AbstractProductValueDataNormalizer;
use Pim\Component\ReferenceData\Model\ReferenceDataInterface;

/**
 * Normalize a reference data into a string
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReferenceDataNormalizer extends AbstractProductValueDataNormalizer
{
    /** @var string[] */
    protected $supportedFormats = ['csv', 'flat'];

    /**
     * {@inheritdoc}
     */
    public function doNormalize($referenceData, $format = null, array $context = [])
    {
        return $referenceData->getCode();
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ReferenceDataInterface && in_array($format, $this->supportedFormats);
    }
}
