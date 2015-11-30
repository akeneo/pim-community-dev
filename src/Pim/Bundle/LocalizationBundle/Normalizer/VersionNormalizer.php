<?php

namespace Pim\Bundle\LocalizationBundle\Normalizer;

use Akeneo\Component\Versioning\Model\Version;
use Pim\Component\Localization\LocaleResolver;
use Pim\Component\Localization\Presenter\PresenterRegistryInterface;
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

    /** @var PresenterRegistryInterface */
    protected $presenterRegistry;

    /** @var LocaleResolver */
    protected $localeResolver;

    /** @var string[] */
    protected $supportedFormats = ['internal_api'];

    /**
     * @param NormalizerInterface        $versionNormalizer
     * @param PresenterRegistryInterface $presenterRegistry
     * @param LocaleResolver             $localeResolver
     */
    public function __construct(
        NormalizerInterface $versionNormalizer,
        PresenterRegistryInterface $presenterRegistry,
        LocaleResolver $localeResolver
    ) {
        $this->versionNormalizer = $versionNormalizer;
        $this->presenterRegistry = $presenterRegistry;
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
        $options = ['locale' => $this->localeResolver->getCurrentLocale()];

        foreach ($changeset as $attribute => $changes) {
            $attributeName = $attribute;
            if (preg_match('/^(?<attribute>[a-zA-Z0-9_]+)-.+$/', $attribute, $matches)) {
                $attributeName = $matches['attribute'];
            }

            $presenter = $this->presenterRegistry->getPresenterByAttributeCode($attributeName);
            if (null !== $presenter) {
                foreach ($changes as $key => $value) {
                    $changeset[$attribute][$key] = $presenter->present($value, $options);
                }
            }
        }

        return $changeset;
    }
}
