<?php

namespace Akeneo\Pim\Enrichment\Component\Comment\Normalizer\Standard;

use Akeneo\Pim\Enrichment\Component\Comment\Model\CommentInterface;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerAwareTrait;

/**
 * Standard Comment normalizer
 *
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CommentNormalizer implements NormalizerInterface, SerializerAwareInterface, CacheableSupportsMethodInterface
{
    use SerializerAwareTrait;

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
            'author'       => $this->normalizeAuthor($comment),
            'body'         => $comment->getBody(),
            'created'      => $this->serializer->normalize($comment->getCreatedAt(), 'standard', $context),
            'replied'      => $this->serializer->normalize($comment->getRepliedAt(), 'standard', $context),
            'replies'      => $this->normalizeChildren($comment->getChildren()->toArray(), $context),
        ];

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof CommentInterface && $format === 'standard';
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }

    /**
     * Normalize the children comments of the comment.
     *
     * @param array $children
     * @param array $context
     *
     * @return array
     */
    protected function normalizeChildren(array $children, array $context = [])
    {
        $comments = [];
        foreach ($children as $child) {
            $comments[] = $this->serializer->normalize($child, 'standard', $context);
        }

        return $comments;
    }

    private function normalizeAuthor(Commentinterface $comment): array
    {
        if (null === $comment->getAuthor()) {
            return [
                'username' => null,
                'fullName' => null,
                'avatar' => null,
            ];
        }

        return [

            'username' => $comment->getAuthor()->getUsername(),
            'fullName' => sprintf(
                '%s %s',
                $comment->getAuthor()->getFirstName(),
                $comment->getAuthor()->getLastName()
            ),
            'avatar' => $comment->getAuthor()->getImagePath(),
        ];
    }
}
