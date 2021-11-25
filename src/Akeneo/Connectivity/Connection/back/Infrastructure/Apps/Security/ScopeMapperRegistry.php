<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Security;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ScopeMapperRegistry
{
    /** @var array{string: ScopeMapperInterface} */
    private iterable $scopeMappers;

    /** @var array{string: string} */
    private array $scopesToMapper;

    public function __construct(iterable $scopeMappers)
    {
        $scopesToMapper = [];
        foreach ($scopeMappers as $entity => $scopeMapper) {
            if (!$scopeMapper instanceof ScopeMapperInterface) {
                throw new \InvalidArgumentException(
                    \sprintf(
                        '%s needs only %s',
                        self::class,
                        ScopeMapperInterface::class
                    )
                );
            }
            foreach ($scopeMapper->getAllScopes() as $scope) {
                $scopesToMapper[$scope] = $entity;
            }
        }
        $this->scopeMappers = $scopeMappers;
        $this->scopesToMapper = $scopesToMapper;
    }

    public function getAllScopes(): array
    {
        $scopes = [];
        foreach ($this->scopeMappers as $scopeMapper) {
            \array_push($scopes, ...$scopeMapper->getAllScopes());
        }

        return \array_values(\array_unique($scopes));
    }

    /**
     * Provides all information needed to display scopes.
     * Filters the scopes by keeping the higher hierarchy scope.
     *
     * @param string[] $scopeList
     *
     * @return array<
     *     array{
     *         icon: string,
     *         type: string,
     *         entities: string,
     *     }
     * >
     */
    public function getMessages(array $scopeList): array
    {
        $lowerScopes = [];
        foreach ($scopeList as $scope) {
            \array_push($lowerScopes, ...$this->getLowerHierarchyScopes($scope));
        }

        $filteredScopes = $this->filterByKeepingHighestLevels(\array_unique($scopeList), $lowerScopes);

        $messages = [];
        foreach ($filteredScopes as $scope) {
            if (null === $scopeMapper = $this->getScopeMapper($scope)) {
                continue;
            }
            $messages[] = $scopeMapper->getMessage($scope);
        }

        return $messages;
    }

    /**
     * Provides acls that correspond to the given scopes from the bottom of the hierarchy.
     *
     * @param string[] $scopeList
     *
     * @return string[]
     */
    public function getAcls(array $scopeList): array
    {
        $fullScopes = $scopeList;
        foreach ($scopeList as $scope) {
            \array_push($fullScopes, ...$this->getLowerHierarchyScopes($scope));
        }
        $fullScopes = \array_unique($fullScopes);

        $acls = [];
        foreach ($fullScopes as $scope) {
            if (null === $scopeMapper = $this->getScopeMapper($scope)) {
                continue;
            }
            \array_push($acls, ...$scopeMapper->getAcls($scope));
        }

        return $acls;
    }

    private function filterByKeepingHighestLevels(array $scopeList, array $lowerLevelScopes): array
    {
        return \array_values(
            \array_filter($scopeList, fn(string $scope) => !\in_array($scope, $lowerLevelScopes))
        );
    }

    private function getLowerHierarchyScopes(string $scope): array
    {
        if (null === $scopeMapper = $this->getScopeMapper($scope)) {
            return [];
        }

        return $scopeMapper->getLowerHierarchyScopes($scope);
    }

    private function getScopeMapper(string $scope): ?ScopeMapperInterface
    {
        if (!\array_key_exists($scope, $this->scopesToMapper)) {
            return null;
        }

        $scopeMapperIndex = $this->scopesToMapper[$scope];
        if (!\array_key_exists($scopeMapperIndex, $this->scopeMappers)) {
            return null;
        }

        return $this->scopeMappers[$scopeMapperIndex];
    }
}
