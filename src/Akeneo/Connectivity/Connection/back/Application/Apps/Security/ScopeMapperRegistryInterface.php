<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Apps\Security;

interface ScopeMapperRegistryInterface
{
    /**
     * @return string[]
     */
    public function getAllScopes(): array;

    /**
     * Provides all information needed to display scopes.
     * Filters the scopes by keeping the higher hierarchy scope.
     *
     * @param string[] $scopeList
     *
     * @throw \LogicArgumentException if the given scope does not exist
     *
     * @return array<
     *     array{
     *         icon: string,
     *         type: string,
     *         entities: string,
     *     }
     * >
     */
    public function getMessages(array $scopeList): array;

    /**
     * Provides acls that correspond to the given scopes from the bottom of the hierarchy.
     *
     * @param string[] $scopeList
     *
     * @throw \LogicArgumentException if the given scope does not exist
     *
     * @return string[]
     */
    public function getAcls(array $scopeList): array;

    /**
     * Given an array of scopes returns list of all inferred scopes from that list
     *
     * @param string[] $scopeList
     * @return string[]
     */
    public function getExhaustiveScopes(array $scopeList): array;
}
