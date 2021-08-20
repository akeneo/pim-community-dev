<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Session;

use Akeneo\Connectivity\Connection\Application\Apps\AppAuthorizationSessionInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AppAuthorization;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AppAuthorizationSession implements AppAuthorizationSessionInterface
{
    private const SESSION_PREFIX = '_app_auth_';

    private SessionInterface $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * The App authorization request, on initialization, is stored in session.
     * This way, it can be accessed and updated during all the steps of the activation wizard.
     */
    public function initialize(AppAuthorization $authorization): void
    {
        $key = $this->getSessionKey($authorization->clientId);

        $this->session->set($key, json_encode($authorization->normalize()));
    }

    /**
     * The session key includes the client_id.
     * It will prevent any override when sereval activations of different apps are started during the same session.
     */
    private function getSessionKey(string $clientId): string
    {
        return sprintf('%s%s', self::SESSION_PREFIX, $clientId);
    }
}
