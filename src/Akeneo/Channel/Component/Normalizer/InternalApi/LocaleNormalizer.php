<?php

namespace Akeneo\Channel\Component\Normalizer\InternalApi;

use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Locale normalizer
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LocaleNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    /** @var string[] */
    protected $supportedFormats = ['internal_api'];

    /** @var UserContext */
    protected $userContext;

    /**
     * @param UserContext $userContext
     */
    public function __construct(UserContext $userContext)
    {
        $this->userContext = $userContext;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($locale, $format = null, array $context = [])
    {
        return [
            'code'     => $locale->getCode(),
            'label'    => $this->getLocaleLabel($locale->getCode()),
            'region'   => \Locale::getDisplayRegion($locale->getCode()),
            'language' => \Locale::getDisplayLanguage($locale->getCode()),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof LocaleInterface && in_array($format, $this->supportedFormats);
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }

    /**
     * Returns the label of a locale in the specified language
     *
     * @param string $code        the code of the locale to translate
     * @param string $translateIn the locale in which the label should be translated (if null, user locale will be used)
     *
     * @return string
     */
    private function getLocaleLabel($code, $translateIn = null)
    {
        $translateIn = $translateIn ?: $this->userContext->getUiLocale()->getCode();

        return \Locale::getDisplayName($code, $translateIn);
    }
}
