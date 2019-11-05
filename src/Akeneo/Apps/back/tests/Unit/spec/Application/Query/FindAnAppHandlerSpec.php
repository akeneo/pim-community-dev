<?php

declare(strict_types=1);

namespace spec\Akeneo\Apps\Application\Query;

use Akeneo\Apps\Application\Query\FindAnAppHandler;
use Akeneo\Apps\Application\Query\FindAnAppQuery;
use Akeneo\Apps\Domain\Model\Read\App;
use Akeneo\Apps\Domain\Model\Read\AppWithCredentials;
use Akeneo\Apps\Domain\Model\ValueObject\FlowType;
use Akeneo\Apps\Domain\Persistence\Query\SelectAppWithCredentialsByCodeQuery;
use Akeneo\Apps\Domain\Persistence\Repository\AppRepository;
use PhpSpec\ObjectBehavior;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class FindAnAppHandlerSpec extends ObjectBehavior
{
    public function let(SelectAppWithCredentialsByCodeQuery $selectAppWithCredentialsByCodeQuery)
    {
        $this->beConstructedWith($selectAppWithCredentialsByCodeQuery);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(FindAnAppHandler::class);
    }

    public function it_returns_an_app($selectAppWithCredentialsByCodeQuery)
    {
        $app = new AppWithCredentials('bynder', 'Bynder DAM', FlowType::OTHER, 'client_id', 'secret');

        $selectAppWithCredentialsByCodeQuery->execute('bynder')->willReturn($app);

        $query = new FindAnAppQuery('bynder');
        $this->handle($query)->shouldReturn($app);
    }

    public function it_returns_null_when_the_app_does_not_exists($selectAppWithCredentialsByCodeQuery)
    {
        $selectAppWithCredentialsByCodeQuery->execute('bynder')->willReturn(null);

        $query = new FindAnAppQuery('bynder');
        $this->handle($query)->shouldReturn(null);
    }
}
