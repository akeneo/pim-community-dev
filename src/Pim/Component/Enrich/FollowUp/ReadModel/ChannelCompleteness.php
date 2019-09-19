<?php
declare(strict_types=1);

namespace Pim\Component\Enrich\FollowUp\ReadModel;

/**
 * ChannelCompleteness class represents the completeness for a channel to show it in the dashboard
 *
 * @author    Elodie Raposo <elodie.raposo@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChannelCompleteness
{
    /** @var string */
    private $channel;

    /** @var int */
    private $numberOfCompleteProducts;

    /** @var int */
    private $numberTotalOfProducts;

    /** @var LocaleCompleteness[] */
    private $localeCompletenesses;

    public function __construct(
        string $channel,
        int $numberOfCompleteProducts,
        int $numberTotalOfProducts,
        array $localeCompletenesses
    ) {
        $this->channel = $channel;
        $this->numberOfCompleteProducts = $numberOfCompleteProducts;
        $this->numberTotalOfProducts = $numberTotalOfProducts;
        $this->localeCompletenesses = $localeCompletenesses;
    }

    public function channel(): string
    {
        return $this->channel;
    }

    public function numberOfCompleteProducts(): int
    {
        return $this->numberOfCompleteProducts;
    }

    public function numberTotalOfProducts(): int
    {
        return $this->numberTotalOfProducts;
    }

    public function localeCompletenesses(): array
    {
        return $this->localeCompletenesses;
    }

    public function toArray(): array
    {
        $locales = [];
        foreach ($this->localeCompletenesses as $localeCompleteness) {
            $locales = array_merge($locales, $localeCompleteness->toArray());
        }

        return [
            'total' => $this->numberTotalOfProducts,
            'complete' => $this->numberOfCompleteProducts,
            'locales' => $locales
        ];
    }
}
