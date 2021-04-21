<?php


namespace Akeneo\Queries;


use spec\Akeneo\Tool\Bundle\ElasticsearchBundle\ClientRegistrySpec;

final class GetUserByIdQuery
{
    private int $id;

    public function __construct(int $id)
    {
        $this->id = $id;
    }

    public function getId(): int
    {
        return $this->id;
    }
}
