<?php

namespace Pim\Bundle\NotificationBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Pim\Bundle\CatalogBundle\Version;

/**
 * New version controller
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VersionController
{
    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /** @var $updateServerUrl */
    protected $updateServerUrl;

    /**
     * @param TokenStorageInterface $tokenStorage
     * @param string                $updateServerUrl
     */
    public function __construct(TokenStorageInterface $tokenStorage, $updateServerUrl)
    {
        $this->tokenStorage    = $tokenStorage;
        $this->updateServerUrl = $updateServerUrl;
    }

    /**
     * Collects data required to fetch new version
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function collectDataAction(Request $request)
    {
        $user    = $this->getUser();
        $userId  = $user->getId();
        $host    = $request->getHost();

        $edition = Version::EDITION;
        $version = Version::VERSION;

        // TODO: extract in a data collector
        // TODO: deal with EE version
        // TODO: spec / behat

        /*
        if (class_exists('PimEnterprise\Bundle\CatalogBundle\Version')) {
            $currentVersion = sprintf('%s-%s', PimEnterprise\Bundle\CatalogBundle\Version::EDITION, PimEnterprise\Bundle\CatalogBundle\Version::VERSION);
        }*/

        $matches = [];
        preg_match('/^(?P<minor>\d.\d)/', $version, $matches);
        $minorVersion = $matches['minor'];
        $minorVersionKey = sprintf('%s-%s', $edition, $minorVersion);

        $data = [
            'pim_edition' => $edition,
            'pim_version' => $version,
            'pim_host'    => $host,
            'user_id'     => $userId,
            'php_version' => phpversion(),
            'os_version'  => php_uname()
        ];

        $queryParams = http_build_query($data);
        $updateUrl = sprintf('%s/%s?%s', $this->updateServerUrl, $minorVersionKey, $queryParams);

        $response = [
            'update_url' => $updateUrl
        ];

        return new JsonResponse($response);
    }

    /**
     * Get a user from the Security Context
     *
     * @return UserInterface|null
     */
    protected function getUser()
    {
        if (null === $token = $this->tokenStorage->getToken()) {
            return null;
        }

        if (!is_object($user = $token->getUser())) {
            return null;
        }

        return $user;
    }
}
