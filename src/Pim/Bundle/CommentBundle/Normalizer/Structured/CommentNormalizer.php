<?php

namespace Pim\Bundle\CommentBundle\Normalizer\Structured;

use Pim\Bundle\CommentBundle\Model\CommentInterface;
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

    /**
     * {@inheritdoc}
     */
    public function normalize($comment, $format = null, array $context = [])
    {
        if (!$this->serializer instanceof NormalizerInterface) {
            throw new \LogicException('Serializer must be a normalizer');
        }

        $data = [
            'id'           => $comment->getId(),
            'resourceName' => $comment->getResourceName(),
            'resourceId'   => $comment->getResourceId(),
            'author'       => [
                'username' => $comment->getAuthor()->getUsername(),
                'fullName' => $comment->getAuthor()->getFirstName() . ' ' . $comment->getAuthor()->getLastName()
            ],
            'body'         => $comment->getBody(),
            'created'      => $this->serializer->normalize($comment->getCreatedAt(), 'json', ['format' => 'M jS g:ia']),
            'replied'      => $this->serializer->normalize($comment->getRepliedAt(), 'json', ['format' => 'M jS g:ia']),
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
