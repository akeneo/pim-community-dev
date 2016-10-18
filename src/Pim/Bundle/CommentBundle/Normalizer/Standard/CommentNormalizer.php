<?php

namespace Pim\Bundle\CommentBundle\Normalizer\Standard;

use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\CommentBundle\Model\CommentInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\SerializerAwareNormalizer;

/**
 * Structured Comment normalizer
 *
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CommentNormalizer extends SerializerAwareNormalizer implements NormalizerInterface
{
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
                'fullName' => sprintf('%s %s', $comment->getAuthor()->getFirstName(), $comment->getAuthor()->getLastName()),
            ],
            'body'         => $comment->getBody(),
            'created'      => $this->serializer->normalize($comment->getCreatedAt(), 'standard', $context),
            'replied'      => $this->serializer->normalize($comment->getRepliedAt(), 'standard', $context),
            'replies'      => $this->normalizeChildren($comment->getChildren(), $context),
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

    /**
     * Normalize the children comments of the comment.
     *
     * @param ArrayCollection $children
     * @param array $context
     *
     * @return ArrayCollection
     */
    protected function normalizeChildren(ArrayCollection $children, array $context = [])
    {
        $comments = [];
        foreach ($children as $child) {
            $comments[] = $this->serializer->normalize($child, 'standard', $context);
        }

        return $comments;
    }
}
