<?php

declare(strict_types=1);

namespace spec\Pim\Bundle\EnrichBundle\Storage\ElasticsearchAndSql\FollowUp;

use Akeneo\Bundle\ElasticsearchBundle\Client;
use Doctrine\DBAL\Connection;
use PhpSpec\ObjectBehavior;
use Pim\Component\Enrich\FollowUp\Query\GetCompletenessPerChannelAndLocaleInterface;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetCompletenessPerChannelAndLocaleSpec extends ObjectBehavior
{
    function let(Connection $connection, Client $client)
    {
        $this->beConstructedWith($connection, $client, 'index');
    }

    function it_implements()
    {
        $this->shouldImplement(GetCompletenessPerChannelAndLocaleInterface::class);
    }
}
