<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi;

use Akeneo\Pim\Enrichment\Component\Product\Localization\Presenter\PresenterRegistryInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Component\Localization\Presenter\PresenterInterface;
use Akeneo\Tool\Component\Versioning\Model\Version;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Akeneo\UserManagement\Bundle\Manager\UserManager;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\Translation\LocaleAwareInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Webmozart\Assert\Assert;

/**
 * Version normalizer
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VersionNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    protected UserManager $userManager;
    protected TranslatorInterface $translator;
    protected LocaleAwareInterface $localeAware;
    protected PresenterRegistryInterface $presenterRegistry;

    /** @var string[] */
    protected array $supportedFormats = ['internal_api'];

    protected array $authorCache = [];
    protected PresenterInterface $datetimePresenter;
    protected AttributeRepositoryInterface $attributeRepository;
    protected UserContext $userContext;

    const ATTRIBUTE_HEADER_SEPARATOR = "-";

    public function __construct(
        UserManager $userManager,
        TranslatorInterface $translator,
        LocaleAwareInterface $localeAware,
        PresenterInterface $datetimePresenter,
        PresenterRegistryInterface $presenterRegistry,
        AttributeRepositoryInterface $attributeRepository,
        UserContext $userContext
    ) {
        $this->userManager = $userManager;
        $this->translator = $translator;
        $this->localeAware = $localeAware;
        $this->datetimePresenter = $datetimePresenter;
        $this->presenterRegistry = $presenterRegistry;
        $this->attributeRepository = $attributeRepository;
        $this->userContext = $userContext;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($version, $format = null, array $context = [])
    {
        $context = array_merge($context, ['locale' => $this->localeAware->getLocale()]);

        try {
            $timezone = $this->userContext->getUserTimezone();
            $loggedAtContext = array_merge($context, ['timezone' => $timezone]);
        } catch (\RuntimeException $exception) {
            $loggedAtContext = $context;
        }

        return [
            'id'           => $version->getId(),
            'author'       => $this->normalizeAuthor($version->getAuthor()),
            'resource_id'  => (string) $version->getResourceId(),
            'snapshot'     => $version->getSnapshot(),
            'changeset'    => $this->convertChangeset($version->getChangeset(), $context),
            'context'      => $version->getContext(),
            'version'      => $version->getVersion(),
            'logged_at'    => $this->datetimePresenter->present($version->getLoggedAt(), $loggedAtContext),
            'pending'      => $version->isPending(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Version && in_array($format, $this->supportedFormats);
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }

    /**
     * @param string $author
     *
     * @return string
     */
    protected function normalizeAuthor($author)
    {
        if (!isset($this->authorCache[$author])) {
            $user = $this->userManager->findUserByUsername($author);

            if (null === $user) {
                $userName = sprintf('%s - %s', $author, $this->translator->trans('pim_user.user.removed_user'));
            } else {
                Assert::isInstanceOf($user, UserInterface::class);
                $userName = sprintf('%s %s', $user->getFirstName(), $user->getLastName());
            }

            $this->authorCache[$author] = $userName;
        }

        return $this->authorCache[$author];
    }

    /**
     * Localize the changeset values
     *
     * @param array $changeset
     * @param array $context
     *
     * @return array
     */
    protected function convertChangeset(array $changeset, array $context)
    {
        $attributeCodes = [];
        foreach (array_keys($changeset) as $valueHeader) {
            $attributeCode = $this->extractAttributeCode($valueHeader);

            $attributeCodes[$attributeCode] = true;
        }

        $attributeTypes = $this->attributeRepository->getAttributeTypeByCodes(array_keys($attributeCodes));

        foreach ($changeset as $valueHeader => $valueChanges) {
            $context['versioned_attribute'] = $valueHeader;
            $attributeCode = $this->extractAttributeCode($valueHeader);
            $context['attribute'] = $attributeCode;

            if (!isset($attributeTypes[$attributeCode])) {
                continue;
            }

            $presenter = $this->presenterRegistry->getPresenterByAttributeType($attributeTypes[$attributeCode]);
            if (null === $presenter) {
                continue;
            }

            foreach ($valueChanges as $key => $value) {
                $changeset[$valueHeader][$key] = $presenter->present($value, $context);
            }
        }

        return $changeset;
    }

    /**
     * Extract the attribute code from the versioning value header.
     * For example, in "price-EUR", the attribute code is "price".
     * For "desc-ecom-en_US", this is "desc".
     */
    protected function extractAttributeCode($valueHeader)
    {
        if (($separatorPos = strpos($valueHeader, self::ATTRIBUTE_HEADER_SEPARATOR)) !== false) {
            return substr($valueHeader, 0, $separatorPos);
        }

        return $valueHeader;
    }
}
