<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Apps\Command;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddAclToRolesCommand
{
    /**
     * @param array<string> $roles
     */
    public function __construct(
        private string $acl,
        private array $roles,
    ) {
    }

    public function getAcl(): string
    {
        return $this->acl;
    }

    /**
     * @return array<string>
     */
    public function getRoles(): array
    {
        return $this->roles;
    }
}
