<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Client\Fos;

use Akeneo\Connectivity\Connection\Application\Settings\Service\CreateClientInterface;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\Read\Client;
use FOS\OAuthServerBundle\Model\ClientManagerInterface;
use OAuth2\OAuth2;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CreateClient implements CreateClientInterface
{
    /** @var ClientManagerInterface */
    private $clientManager;

    public function __construct(ClientManagerInterface $clientManager)
    {
        $this->clientManager = $clientManager;
    }

    public function execute(string $label): Client
    {
        $fosClient = $this->clientManager->createClient();
        $fosClient->setLabel($label);
        $fosClient->setAllowedGrantTypes([OAuth2::GRANT_TYPE_USER_CREDENTIALS, OAuth2::GRANT_TYPE_REFRESH_TOKEN]);
        $this->clientManager->updateClient($fosClient);

        return new Client($fosClient->getId(), $fosClient->getPublicId(), $fosClient->getSecret());
    }
}
