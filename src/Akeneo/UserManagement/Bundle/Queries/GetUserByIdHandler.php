<?php


namespace Akeneo\UserManagement\Bundle\Queries;

use Akeneo\Query\GetUserByIdQuery;
use Akeneo\Query\User;
use Akeneo\UserManagement\Component\Model\UserInterface;
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

    // TODO: Handle error
    public function __invoke(GetUserByIdQuery $query) :User
    {
        /** @var UserInterface $user */
        $user = $this->repository->find($query->getId());

        return new User($user->getId(), $user->getUsername());
    }
}

