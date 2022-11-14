<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Comment\Normalizer\Standard;

use Akeneo\Pim\Enrichment\Component\Comment\Model\CommentInterface;
use Akeneo\Pim\Enrichment\Component\Comment\Normalizer\Standard\CommentNormalizer;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class CommentNormalizerSpec extends ObjectBehavior
{
    function let(NormalizerInterface $normalizer)
    {
        $this->setNormalizer($normalizer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(CommentNormalizer::class);
        $this->shouldImplement(NormalizerAwareInterface::class);
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_supports_standard_normalization(CommentInterface $comment)
    {
        $this->supportsNormalization($comment, 'standard')->shouldReturn(true);
        $this->supportsNormalization(new \stdClass(), 'standard')->shouldReturn(false);
        $this->supportsNormalization($comment, 'other_format')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'other_format')->shouldReturn(false);
    }

    function it_normalizes_comment(
        NormalizerInterface $normalizer,
        CommentInterface $comment,
        CommentInterface $childComment,
        UserInterface $author
    ) {
        $dateTime = new \DateTime('2015-05-23 15:55:50');

        $normalizer
            ->normalize($dateTime, 'standard', [])
            ->shouldBeCalled()
            ->willReturn('2015-05-23T15:55:50+01:00');

        $children = new ArrayCollection();
        $children->add($childComment);

        $normalizer
            ->normalize($childComment, 'standard', [])
            ->shouldBeCalled()
            ->willReturn([
                'id'           => 2,
                'resourceName' => 'Product',
                'resourceId'   => '100',
                'resourceUuid' => 'b7d38dc6-5166-46c5-ae7f-2643d8ef4edc',
                'author'       => [
                    'username' => 'mary',
                    'fullName' => 'Mary Dia',
                ],
                'body'         => 'Body of the child comment',
                'created'      => '2015-05-22T14:00:00+01:00',
                'replied'      => '2015-05-22T14:00:00+01:00',
                'replies'      => [],
            ]);

        $author->getUserIdentifier()->willReturn('julia');
        $author->getFirstName()->willReturn('Julia');
        $author->getLastName()->willReturn('Doe');
        $author->getImagePath()->willReturn('/path/to/image');

        $comment->getId()->willReturn(1);
        $comment->getResourceName()->willReturn('Product');
        $comment->getResourceId()->willReturn('100');
        $comment->getResourceUuid()->willReturn(Uuid::fromString('b7d38dc6-5166-46c5-ae7f-2643d8ef4edc'));
        $comment->getAuthor()->willReturn($author);
        $comment->getBody()->willReturn('Body of the comment');
        $comment->getCreatedAt()->willReturn($dateTime);
        $comment->getRepliedAt()->willReturn($dateTime);
        $comment->getChildren()->willReturn($children);

        $this->normalize($comment, 'standard')->shouldReturn([
            'id'           => 1,
            'resourceName' => 'Product',
            'resourceId'   => '100',
            'resourceUuid'   => 'b7d38dc6-5166-46c5-ae7f-2643d8ef4edc',
            'author'       => [
                'username' => 'julia',
                'fullName' => 'Julia Doe',
                'avatar'   => '/path/to/image',
            ],
            'body'         => 'Body of the comment',
            'created'      => '2015-05-23T15:55:50+01:00',
            'replied'      => '2015-05-23T15:55:50+01:00',
            'replies'      => [
                [
                    'id'           => 2,
                    'resourceName' => 'Product',
                    'resourceId'   => '100',
                    'resourceUuid'   => 'b7d38dc6-5166-46c5-ae7f-2643d8ef4edc',
                    'author'       => [
                        'username' => 'mary',
                        'fullName' => 'Mary Dia',
                    ],
                    'body'         => 'Body of the child comment',
                    'created'      => '2015-05-22T14:00:00+01:00',
                    'replied'      => '2015-05-22T14:00:00+01:00',
                    'replies'      => [],
                ]
           ],
        ]);
    }

    function it_normalizes_a_comment_whose_author_was_removed(
        NormalizerInterface $normalizer,
        CommentInterface $comment
    ) {
        $dateTime = new \DateTime('2015-05-23 15:55:50');
        $normalizer
            ->normalize($dateTime, 'standard', [])
            ->willReturn('2015-05-23T15:55:50+01:00');
        $normalizer->normalize(null, 'standard', [])->willReturn(null);
        $comment->getId()->willReturn(42);
        $comment->getResourceName()->willReturn('Product');
        $comment->getResourceId()->willReturn('100');
        $comment->getResourceUuid()->willReturn(Uuid::fromString('b7d38dc6-5166-46c5-ae7f-2643d8ef4edc'));
        $comment->getAuthor()->willReturn(null);
        $comment->getBody()->willReturn('Lorem ipsum dolor sit amet');
        $comment->getCreatedAt()->willReturn($dateTime);
        $comment->getRepliedAt()->willReturn(null);
        $comment->getChildren()->willReturn(new ArrayCollection());

        $this->normalize($comment)->shouldReturn(
            [
                'id' => 42,
                'resourceName' => 'Product',
                'resourceId' => '100',
                'resourceUuid' => 'b7d38dc6-5166-46c5-ae7f-2643d8ef4edc',
                'author' => [
                    'username' => null,
                    'fullName' => null,
                    'avatar' => null,
                ],
                'body' => 'Lorem ipsum dolor sit amet',
                'created' => '2015-05-23T15:55:50+01:00',
                'replied' => null,
                'replies' => [],
            ]
        );
    }
}
