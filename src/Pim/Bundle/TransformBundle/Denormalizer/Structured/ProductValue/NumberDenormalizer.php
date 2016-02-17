<?php

namespace Pim\Bundle\TransformBundle\Denormalizer\Structured\ProductValue;

use Akeneo\Component\Localization\Localizer\LocalizerInterface;

/**
 * Number denormalizer used following attribute types:
 * - pim_catalog_number
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NumberDenormalizer extends AbstractValueDenormalizer
{
    /** @var LocalizerInterface */
    protected $localizer;

    /**
     * @param string[]           $supportedTypes
     * @param LocalizerInterface $localizer
     */
    public function __construct($supportedTypes, LocalizerInterface $localizer)
    {
        parent::__construct($supportedTypes);

        $this->localizer = $localizer;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        return '' === $data ? null : $this->localizer->localize($data, $context);
    }
}
