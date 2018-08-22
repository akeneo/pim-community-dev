<?php
declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\FollowUp\ReadModel;

/**
 * CompletenessWidget class represents the global completeness to show it in the dashboard
 *
 * @author    Elodie Raposo <elodie.raposo@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CompletenessWidget
{
    /** @var ChannelCompleteness[] */
    private $channelCompletenesses;

    /**
     * @param ChannelCompleteness[] $channelCompletenesses
     */
    public function __construct(array $channelCompletenesses)
    {
        $this->channelCompletenesses = $channelCompletenesses;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $array = [];
        foreach ($this->channelCompletenesses as $channelCompleteness) {
            $array[$channelCompleteness->channel()] = $channelCompleteness->toArray();
        }
        return $array;
    }
}
