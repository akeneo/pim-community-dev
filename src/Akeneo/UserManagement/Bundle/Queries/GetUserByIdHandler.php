<?php


namespace Akeneo\UserManagement\Bundle\Queries;

use Akeneo\Queries\GetUserByIdQuery;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class GetUserByIdHandler implements MessageSubscriberInterface
{

    private UserRepositoryInterface $repository;

    private NormalizerInterface $normalizer;

    public function __construct(UserRepositoryInterface $repository,NormalizerInterface $normalizer)
    {
        $this->repository = $repository;
        $this->normalizer = $normalizer;
    }

    public static function getHandledMessages(): iterable
    {
        yield GetUserByIdQuery::class => [
            'bus' => 'query.bus',
        ];
    }

    public function __invoke(GetUserByIdQuery $query)
    {
        $user = $this->repository->find($query->getId());

        return $this->normalizer->normalize($user);
    }
}

