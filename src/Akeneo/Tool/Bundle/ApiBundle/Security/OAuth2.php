<?php

namespace Akeneo\Tool\Bundle\ApiBundle\Security;

use OAuth2\OAuth2 as BaseOAuth2;
use OAuth2\OAuth2AuthenticateException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OAuth2 extends BaseOAuth2
{
    /**
     * {@inheritdoc}
     */
    public function verifyAccessToken($tokenParam, $scope = null)
    {
        try {
            return parent::verifyAccessToken($tokenParam, $scope);
        } catch (OAuth2AuthenticateException $e) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, $e->getDescription(), $e);
        }
    }
}
