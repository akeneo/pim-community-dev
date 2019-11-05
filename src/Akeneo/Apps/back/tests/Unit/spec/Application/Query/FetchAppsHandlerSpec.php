<?php

declare(strict_types=1);

namespace spec\Akeneo\Apps\Application\Query;

use Akeneo\Apps\Application\Query\FetchAppsHandler;
use Akeneo\Apps\Domain\Model\Read\App;
use Akeneo\Apps\Domain\Model\ValueObject\FlowType;
use Akeneo\Apps\Domain\Persistence\Query\SelectAppsQuery;
use PhpSpec\ObjectBehavior;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class FetchAppsHandlerSpec extends ObjectBehavior
{
    public function let(SelectAppsQuery $selectAppsQuery)
    {
        $this->beConstructedWith($selectAppsQuery);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(FetchAppsHandler::class);
    }

    public function it_fetches_apps($selectAppsQuery)
    {
        $apps = [
            new App('42', 'magento', 'Magento Connector', FlowType::DATA_DESTINATION),
            new App('43', 'bynder', 'Bynder DAM', FlowType::OTHER),
        ];

        $selectAppsQuery->execute()->willReturn($apps);

        $this->query()->shouldReturn($apps);
    }
}
