<?php

namespace Pim\Bundle\CommentBundle\Normalizer\Structured;

use Akeneo\Component\Localization\Presenter\PresenterInterface;
use Pim\Bundle\CommentBundle\Model\CommentInterface;
use Pim\Bundle\EnrichBundle\Resolver\LocaleResolver;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\SerializerAwareNormalizer;

/**
 * Structured Comment normalizer
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CommentNormalizer extends SerializerAwareNormalizer implements NormalizerInterface
{
    /** @var string[] $supportedFormats */
    protected $supportedFormats = ['json', 'xml'];

    /** @var PresenterInterface */
    protected $datetimePresenter;

    /** @var \Pim\Bundle\EnrichBundle\Resolver\LocaleResolver */
    protected $localeResolver;

    /**
     * @param PresenterInterface $datetimePresenter
     * @param LocaleResolver     $localeResolver
     */
    public function __construct(PresenterInterface $datetimePresenter, LocaleResolver $localeResolver)
    {
        $this->datetimePresenter = $datetimePresenter;
        $this->localeResolver    = $localeResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($comment, $format = null, array $context = [])
    {
        if (!$this->serializer instanceof NormalizerInterface) {
            throw new \LogicException('Serializer must be a normalizer');
        }

        $context = ['locale' => $this->localeResolver->getCurrentLocale()];

        $data = [
            'id'           => $comment->getId(),
            'resourceName' => $comment->getResourceName(),
            'resourceId'   => $comment->getResourceId(),
            'author'       => [
                'username' => $comment->getAuthor()->getUsername(),
                'fullName' => $comment->getAuthor()->getFirstName() . ' ' . $comment->getAuthor()->getLastName()
            ],
            'body'         => $comment->getBody(),
            'created'      => $this->datetimePresenter->present($comment->getCreatedAt(), $context),
            'replied'      => $this->datetimePresenter->present($comment->getRepliedAt(), $context),
            'replies'      => $this->serializer->normalize($comment->getChildren(), 'json'),
        ];

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof CommentInterface && in_array($format, $this->supportedFormats);
    }
}
