<?php

namespace Pim\Bundle\EnrichBundle\Normalizer;

use Akeneo\Component\Versioning\Model\Version;
use Oro\Bundle\UserBundle\Entity\UserManager;
use Pim\Component\Localization\Presenter\PresenterInterface;
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

    /** @var string[] */
    protected $supportedFormats = ['internal_api'];

    /** @var array */
    protected $authorCache = [];

    /** @var PresenterInterface */
    protected $datetimePresenter;

    /**
     * @param UserManager         $userManager
     * @param TranslatorInterface $translator
     * @param PresenterInterface  $datetimePresenter
     */
    public function __construct(
        UserManager $userManager,
        TranslatorInterface $translator,
        PresenterInterface $datetimePresenter
    ) {
        $this->userManager       = $userManager;
        $this->translator        = $translator;
        $this->datetimePresenter = $datetimePresenter;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($version, $format = null, array $context = [])
    {
        $context = ['locale' => $this->translator->getLocale()];

        return [
            'id'           => $version->getId(),
            'author'       => $this->normalizeAuthor($version->getAuthor()),
            'resource_id'  => (string) $version->getResourceId(),
            'snapshot'     => $version->getSnapshot(),
            'changeset'    => $version->getChangeset(),
            'context'      => $version->getContext(),
            'version'      => $version->getVersion(),
            'logged_at'    => $this->datetimePresenter->present($version->getLoggedAt(), $context),
            'pending'      => $version->isPending()
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
                $userName = sprintf('%s %s - %s', $user->getFirstName(), $user->getLastName(), $user->getEmail());
            }

            $this->authorCache[$author] = $userName;
        }

        return $this->authorCache[$author];
    }
}
