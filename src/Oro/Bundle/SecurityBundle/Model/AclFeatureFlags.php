<?php

declare(strict_types=1);

namespace Oro\Bundle\SecurityBundle\Model;

use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlags;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AclFeatureFlags
{
    private array $acls = [];

    public function __construct(private FeatureFlags $featureFlags)
    {
    }

    public function add(string $aclName, ?string $feature): void
    {
        $this->acls[$aclName] = $feature;
    }

    public function isAclAvailable(string $aclName): bool
    {
        if (!array_key_exists($aclName, $this->acls)) {
            throw new \InvalidArgumentException("Acl name \"$aclName\" does not exist.");
        }

        if ($this->acls[$aclName] === null) {
            return true;
        }

        return $this->featureFlags->isEnabled($this->acls[$aclName]);
    }

    public function hasAcl(string $aclName): bool
    {
        return array_key_exists($aclName, $this->acls);
    }
}
