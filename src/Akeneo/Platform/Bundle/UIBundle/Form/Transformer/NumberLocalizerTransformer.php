<?php

namespace Akeneo\Platform\Bundle\UIBundle\Form\Transformer;

use Akeneo\Tool\Component\Localization\Localizer\LocalizerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * Transform a number
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NumberLocalizerTransformer implements DataTransformerInterface
{
    /** @var LocalizerInterface */
    protected $localizer;

    /** @var array */
    protected $options;

    /**
     * @param LocalizerInterface $localizer
     * @param array              $options
     */
    public function __construct(LocalizerInterface $localizer, array $options)
    {
        $this->localizer = $localizer;
        $this->options = $options;
    }

    /**
     * Return the number provided. Do nothing because number is still formatted by denormalizer
     *
     * @param string $number
     *
     * @return string
     */
    public function transform($number)
    {
        return $this->localizer->localize($number, $this->options);
    }

    /**
     * Check if number provided respects the pattern of the localizer.
     * If false, throw an exception.
     * It true, delocalize localized number to the default format
     *
     * @param string $number
     *
     * @throws TransformationFailedException
     *
     * @return string
     */
    public function reverseTransform($number)
    {
        $violations = $this->localizer->validate($number, 'code', $this->options);

        if (null === $violations || 0 === $violations->count()) {
            return $this->localizer->delocalize($number, $this->options);
        }

        throw new TransformationFailedException($violations->get(0)->getMessage());
    }
}
