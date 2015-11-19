<?php

namespace Pim\Bundle\LocalizationBundle\Normalizer;

use Pim\Bundle\VersioningBundle\Model\Version;
use Pim\Component\Localization\LocaleResolver;
use Pim\Component\Localization\Localizer\LocalizedAttributeConverterInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Version normalizer including localization methods
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VersionNormalizer implements NormalizerInterface
{
    /** @var NormalizerInterface */
    protected $versionNormalizer;

    /** @var LocalizedAttributeConverterInterface */
    protected $converter;

    /** @var LocaleResolver */
    protected $localeResolver;

    /** @var string[] */
    protected $supportedFormats = ['internal_api'];

    /**
     * @param NormalizerInterface                  $versionNormalizer
     * @param LocalizedAttributeConverterInterface $converter
     * @param LocaleResolver                       $localeResolver
     */
    public function __construct(
        NormalizerInterface $versionNormalizer,
        LocalizedAttributeConverterInterface $converter,
        LocaleResolver $localeResolver
    ) {
        $this->versionNormalizer = $versionNormalizer;
        $this->converter         = $converter;
        $this->localeResolver    = $localeResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $version = $this->versionNormalizer->normalize($object, $format, $context);
        $version['changeset'] = $this->convertChangeset($version['changeset']);

        return $version;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Version && in_array($format, $this->supportedFormats);
    }

    /**
     * Localize the changeset values
     *
     * @param array $changeset
     *
     * @return array
     */
    protected function convertChangeset(array $changeset)
    {
        $formats = $this->localeResolver->getFormats();

        foreach ($changeset as $attribute => $changes) {
            $attributeName = $attribute;
            if (preg_match('/^(?<attribute>[a-zA-Z0-9_]*[a-zA-Z_]+[a-zA-Z0-9_]*)-.+$/', $attribute, $matches)) {
                $attributeName = $matches['attribute'];
            }

            foreach ($changes as $key => $value) {
                $changeset[$attribute][$key] = $this->converter->convertDefaultToLocalizedValue(
                    $attributeName,
                    $value,
                    $formats
                );
            }
        }

        return $changeset;
    }
}
