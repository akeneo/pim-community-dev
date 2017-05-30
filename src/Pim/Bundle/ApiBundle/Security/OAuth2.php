<?php

namespace Pim\Bundle\ApiBundle\Security;

use OAuth2\OAuth2 as BaseOAuth2;
use OAuth2\OAuth2AuthenticateException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

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
            throw new UnprocessableEntityHttpException($e->getDescription(), $e);
        }
    }
}
