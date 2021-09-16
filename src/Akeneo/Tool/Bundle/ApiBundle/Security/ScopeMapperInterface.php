<?php
declare(strict_types=1);

namespace Akeneo\Tool\Bundle\ApiBundle\Security;

interface ScopeMapperInterface
{
    /**
     * @return string[]
     */
    public function getAllScopes(): array;

    /**
     * @return string[]
     */
    public function getAcls(string $scopeName): array;

    /**
     * @param string[] $scopes
     * @return string[]
     */
    public function formalizeScopes(array $scopes): array;

    /**
     * @param string[] $scopeList
     * @return array<array{
     *      icon: string,
     *      type: string,
     *      entities: string,
     * }>
     */
    public function getMessages(array $scopeList): array;
}
