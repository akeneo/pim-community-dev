<?php

namespace Pim\Bundle\TransformBundle\Normalizer\Flat;

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
    /** @var array $supportedFormats */
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
