<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Security;

use Akeneo\Connectivity\Connection\Application\Apps\Security\ScopeMapperRegistryInterface;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ScopeMapperRegistry implements ScopeMapperRegistryInterface
{
    /** @var array<string, ScopeMapperInterface> */
    private iterable $scopeMappers = [];

    public function __construct(iterable $scopeMappers)
    {
        foreach ($scopeMappers as $scopeMapper) {
            if (!$scopeMapper instanceof ScopeMapperInterface) {
                throw new \InvalidArgumentException(
                    \sprintf(
                        '%s must implement %s',
                        self::class,
                        ScopeMapperInterface::class
                    )
                );
            }
            foreach ($scopeMapper->getScopes() as $scope) {
                if (\array_key_exists($scope, $this->scopeMappers)) {
                    throw new \InvalidArgumentException(
                        \sprintf(
                            'The scope "%s" is already supported by the scope mapper "%s".',
                            $scope,
                            $this->scopeMappers[$scope]::class
                        )
                    );
                }
                $this->scopeMappers[$scope] = $scopeMapper;
            }
        }
    }

    /**
     * @return string[]
     */
    public function getAllScopes(): array
    {
        return \array_keys($this->scopeMappers);
    }

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
    public function getMessages(array $scopeList): array
    {
        \sort($scopeList);

        $lowerScopes = [];
        foreach ($scopeList as $scope) {
            \array_push($lowerScopes, ...$this->getScopeMapper($scope)->getLowerHierarchyScopes($scope));
        }

        $filteredScopes = $this->filterByKeepingHighestLevels(\array_unique($scopeList), $lowerScopes);

        $messages = [];
        foreach ($filteredScopes as $scope) {
            if (null !== $message = $this->getScopeMapper($scope)->getMessage($scope)) {
                $messages[] = $message;
            }
        }

        return $messages;
    }

    /**
     * Provides acls that correspond to the given scopes from the bottom of the hierarchy.
     *
     * @param string[] $scopeList
     *
     * @throw \LogicArgumentException if the given scope does not exist
     *
     * @return string[]
     */
    public function getAcls(array $scopeList): array
    {
        \sort($scopeList);

        $fullScopes = $this->getExhaustiveScopes($scopeList);

        $acls = [];
        foreach ($fullScopes as $scope) {
            \array_push($acls, ...$this->getScopeMapper($scope)->getAcls($scope));
        }

        return $acls;
    }

    private function filterByKeepingHighestLevels(array $scopeList, array $lowerLevelScopes): array
    {
        return \array_values(
            \array_filter($scopeList, fn (string $scope): bool => !\in_array($scope, $lowerLevelScopes))
        );
    }

    private function getScopeMapper(string $scope): ScopeMapperInterface
    {
        if (!\array_key_exists($scope, $this->scopeMappers)) {
            throw new \LogicException(\sprintf('The scope "%s" does not exist.', $scope));
        }

        return $this->scopeMappers[$scope];
    }

    /**
     * @inheritDoc
     */
    public function getExhaustiveScopes(array $scopeList): array
    {
        $fullScopes = $scopeList;
        foreach ($scopeList as $scope) {
            \array_push($fullScopes, ...$this->getScopeMapper($scope)->getLowerHierarchyScopes($scope));
        }
        return \array_unique($fullScopes);
    }
}
