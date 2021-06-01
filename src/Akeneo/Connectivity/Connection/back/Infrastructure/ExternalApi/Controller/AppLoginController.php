<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\ExternalApi\Controller;

use Akeneo\Connectivity\Connection\Domain\Settings\Model\Write\Connection;
use Akeneo\Connectivity\Connection\Infrastructure\Service\CreateAppUserWithPermissions;
use Akeneo\Connectivity\Connection\Infrastructure\Service\OAuthScopeValidator;
use FOS\OAuthServerBundle\Controller\AuthorizeController;
use OAuth2\OAuth2;
use OAuth2\OAuth2ServerException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AppLoginController extends AuthorizeController
{
    const PRODUCT_EDIT_SCOPE = 'product:edit';
    use WithCheckClientHash;

    protected OAuth2 $oAuth2Server;
    protected OAuthScopeValidator $scopeValidator;
    protected CreateAppUserWithPermissions $createAppUserWithPermissions;

    public function setCreateAppUserWithPermissions(CreateAppUserWithPermissions $createAppUserWithPermissions): void
    {
        $this->createAppUserWithPermissions = $createAppUserWithPermissions;
    }

    public function setScopeValidator(OAuthScopeValidator $scopeValidator): void
    {
        $this->scopeValidator = $scopeValidator;
    }

    public function setOAuth2Server(OAuth2 $oAuth2Server): void
    {
        $this->oAuth2Server = $oAuth2Server;
        // define only wep api acl permissions
        $this->oAuth2Server->setVariable(
            OAuth2::CONFIG_SUPPORTED_SCOPES,
            self::PRODUCT_EDIT_SCOPE
        );
    }

    public function get(Request $request): Response
    {
        try {
            $this->checkClientHash();
            if ($request->get('scope')) {
                // get scopes for OAuth Apps
                $scopes = explode(',', $request->get('scope'));
                //$this->scopeValidator->validate($scopes);
                $this->createAppUserWithPermissions->handle($scopes);
            }

            $response = $this->authorizeAction($request);

            // create a connection on post authorization process
            return $response;
        } catch (OAuth2ServerException $e) {
            return $e->getHttpResponse();
        } catch (\Exception $e) {
            dd($e);
        }
    }
}