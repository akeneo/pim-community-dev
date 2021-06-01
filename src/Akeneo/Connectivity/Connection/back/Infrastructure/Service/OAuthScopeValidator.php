<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Service;

use Akeneo\UserManagement\Component\Model\User;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\Security\Core\Security;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class OAuthScopeValidator
{
    // 
    private SecurityFacade $securityFacade;
    private Security $security;
    private OAuthScopeTransformer $authScopeTransformer;

    public function __construct(
        SecurityFacade $securityFacade,
        Security $security,
        OAuthScopeTransformer $authScopeTransformer
    ) {
        $this->securityFacade = $securityFacade;
        $this->security = $security;
        $this->authScopeTransformer = $authScopeTransformer;
    }

    public function validate(array $scopes)
    {        
        if (false === $this->isValid($scopes)) {
            throw new \LogicException('The scope is invalid');
        }
    }

    private function isValid(array $scopes): bool
    {
        $aclPermissionIds = $this->authScopeTransformer->transform($scopes);
        foreach ($aclPermissionIds as $aclPermissionId) {
            if (false === $this->securityFacade->isGranted($aclPermissionId)) {
                return false;
            }
        }

        return true;
    }
}