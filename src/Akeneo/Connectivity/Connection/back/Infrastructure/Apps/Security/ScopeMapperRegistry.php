<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Security;

use Akeneo\Connectivity\Connection\Domain\Apps\ScopeMapperInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\ScopeMapperRegistryInterface;

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
                        sprintf(
                            'The scope "%s" is already supported by the scope mapper "%s".',
                            $scope,
                            get_class($this->scopeMappers[$scope])
                        )
                    );
                }
                $this->scopeMappers[$scope] = $scopeMapper;
            }
        }
    }

    public function getAllScopes(): array
    {
        return \array_keys($this->scopeMappers);
    }

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
            $messages[] = $this->getScopeMapper($scope)->getMessage($scope);
        }

        return $messages;
    }

    public function getAcls(array $scopeList): array
    {
        \sort($scopeList);

        $fullScopes = $scopeList;
        foreach ($scopeList as $scope) {
            \array_push($fullScopes, ...$this->getScopeMapper($scope)->getLowerHierarchyScopes($scope));
        }
        $fullScopes = \array_unique($fullScopes);

        $acls = [];
        foreach ($fullScopes as $scope) {
            \array_push($acls, ...$this->getScopeMapper($scope)->getAcls($scope));
        }

        return $acls;
    }

    private function filterByKeepingHighestLevels(array $scopeList, array $lowerLevelScopes): array
    {
        return \array_values(
            \array_filter($scopeList, fn (string $scope) => !\in_array($scope, $lowerLevelScopes))
        );
    }

    private function getScopeMapper(string $scope): ScopeMapperInterface
    {
        if (!\array_key_exists($scope, $this->scopeMappers)) {
            throw new \LogicException(sprintf('The scope "%s" does not exist.', $scope));
        }

        return $this->scopeMappers[$scope];
    }
}
