<?php

namespace Pim\Bundle\EnrichBundle\Normalizer;

use Akeneo\Tool\Component\Localization\Presenter\PresenterInterface;
use Akeneo\Tool\Component\Versioning\Model\Version;
use Pim\Bundle\UserBundle\Manager\UserManager;
use Pim\Component\Catalog\Localization\Presenter\PresenterRegistryInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Version normalizer
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VersionNormalizer implements NormalizerInterface
{
    /** @var UserManager */
    protected $userManager;

    /** @var TranslatorInterface */
    protected $translator;

    /** @var PresenterRegistryInterface */
    protected $presenterRegistry;

    /** @var string[] */
    protected $supportedFormats = ['internal_api'];

    /** @var array */
    protected $authorCache = [];

    /** @var PresenterInterface */
    protected $datetimePresenter;

    /**
     * @param UserManager                $userManager
     * @param TranslatorInterface        $translator
     * @param PresenterInterface         $datetimePresenter
     * @param PresenterRegistryInterface $presenterRegistry
     */
    public function __construct(
        UserManager $userManager,
        TranslatorInterface $translator,
        PresenterInterface $datetimePresenter,
        PresenterRegistryInterface $presenterRegistry
    ) {
        $this->userManager = $userManager;
        $this->translator = $translator;
        $this->datetimePresenter = $datetimePresenter;
        $this->presenterRegistry = $presenterRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($version, $format = null, array $context = [])
    {
        $context = array_merge($context, ['locale' => $this->translator->getLocale()]);

        return [
            'id'           => $version->getId(),
            'author'       => $this->normalizeAuthor($version->getAuthor()),
            'resource_id'  => (string) $version->getResourceId(),
            'snapshot'     => $version->getSnapshot(),
            'changeset'    => $this->convertChangeset($version->getChangeset(), $context),
            'context'      => $version->getContext(),
            'version'      => $version->getVersion(),
            'logged_at'    => $this->datetimePresenter->present($version->getLoggedAt(), $context),
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
                $userName = sprintf('%s - %s', $author, $this->translator->trans('Removed user'));
            } else {
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
        foreach ($changeset as $attribute => $changes) {
            $context['versioned_attribute'] = $attribute;
            $attributeName = $attribute;
            if (preg_match('/^(?<attribute>[a-zA-Z0-9_]+)-.+$/', $attribute, $matches)) {
                $attributeName = $matches['attribute'];
            }

            $presenter = $this->presenterRegistry->getPresenterByAttributeCode($attributeName);
            if (null !== $presenter) {
                foreach ($changes as $key => $value) {
                    $changeset[$attribute][$key] = $presenter->present($value, $context);
                }
            }
        }

        return $changeset;
    }
}
