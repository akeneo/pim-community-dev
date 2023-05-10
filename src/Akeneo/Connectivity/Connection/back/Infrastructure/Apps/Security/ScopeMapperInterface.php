<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Security;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ScopeMapperInterface
{
    /**
     * @return string[]
     */
    public function getScopes(): array;

    /**
     * @throw \InvalidArgumentException if the given scope does not exist.
     *
     * @return string[]
     */
    public function getAcls(string $scopeName): array;

    /**
     * @throw \InvalidArgumentException if the given scope does not exist.
     *
     * @return array{icon: string, type: string, entities: string}|null
     */
    public function getMessage(string $scopeName): ?array;

    /**
     * @throw \InvalidArgumentException if the given scope does not exist.
     *
     * @return string[]
     */
    public function getLowerHierarchyScopes(string $scopeName): array;
}
