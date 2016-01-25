<?php

namespace Pim\Bundle\EnrichBundle\Normalizer;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\Versioning\Model\Version;
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
    /** @var IdentifiableObjectRepositoryInterface */
    protected $repository;

    /** @var TranslatorInterface */
    protected $translator;

    /** @var string[] */
    protected $supportedFormats = ['internal_api'];

    /** @var array */
    protected $authorCache = [];

    /**
     * @param IdentifiableObjectRepositoryInterface $repository
     * @param TranslatorInterface                   $translator
     */
    public function __construct(IdentifiableObjectRepositoryInterface $repository, TranslatorInterface $translator)
    {
        $this->repository = $repository;
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($version, $format = null, array $context = [])
    {
        return [
            'id'           => $version->getId(),
            'author'       => $this->normalizeAuthor($version->getAuthor()),
            'resource_id'  => (string) $version->getResourceId(),
            'snapshot'     => $version->getSnapshot(),
            'changeset'    => $version->getChangeset(),
            'context'      => $version->getContext(),
            'version'      => $version->getVersion(),
            'logged_at'    => $version->getLoggedAt()->format('Y-m-d H:i:s'),
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
            $user = $this->repository->findOneByIdentifier($author);

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
