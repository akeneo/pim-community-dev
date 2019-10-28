<?php

namespace Oro\Bundle\PimDataGridBundle\Normalizer;

use Akeneo\Tool\Component\Localization\Presenter\PresenterInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalize a DateTime into an localized date
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DateTimeNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    /** @var NormalizerInterface */
    protected $standardNormalizer;

    /** @var PresenterInterface */
    protected $presenter;

    /** @var UserContext */
    protected $userContext;

    /**
     * @param NormalizerInterface $standardNormalizer
     * @param PresenterInterface  $presenter
     * @param UserContext         $userContext
     */
    public function __construct(
        NormalizerInterface $standardNormalizer,
        PresenterInterface $presenter,
        UserContext $userContext
    ) {
        $this->standardNormalizer = $standardNormalizer;
        $this->presenter = $presenter;
        $this->userContext = $userContext;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($date, $format = null, array $context = [])
    {
        $stdProductValue = $this->standardNormalizer->normalize($date, 'standard', $context);

        $stdProductValue = $this->presenter->present(
            $stdProductValue,
            [
                'locale'   => $this->userContext->getUiLocaleCode(),
                'timezone' => $this->userContext->getUserTimezone(),
            ]
        );

        return $stdProductValue;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof \DateTimeInterface && 'datagrid' === $format;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
