<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\Apps\Controller\Public;

use Akeneo\Catalogs\ServiceAPI\Messenger\QueryBus;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\FindOneConnectedAppByUserIdentifierQueryInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Controller\Public\ConfigureCatalogAction;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConfigureCatalogActionSpec extends ObjectBehavior
{
    public function let(
        RouterInterface $router,
        SecurityFacade $security,
        QueryBus $catalogQueryBus,
        FindOneConnectedAppByUserIdentifierQueryInterface $findOneConnectedAppByUserIdentifierQuery,
    )
    {
        $this->beConstructedWith(

        );
    }

    public function it_is_an_configure_catalog_action(): void
    {
        $this->beAnInstanceOf(ConfigureCatalogAction::class);
    }
}
