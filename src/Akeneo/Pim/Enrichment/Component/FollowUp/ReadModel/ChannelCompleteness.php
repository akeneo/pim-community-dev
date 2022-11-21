<?php
declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\FollowUp\ReadModel;

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
    private $channelCode;

    /** @var array */
    private $channelLabels;

    /** @var int */
    private $numberOfCompleteProducts;

    /** @var int */
    private $numberTotalOfProducts;

    /** @var LocaleCompleteness[] */
    private $localeCompletenesses;

    /**
     * @param string $channelCode
     * @param int $numberOfCompleteProducts
     * @param int $numberTotalOfProducts
     * @param LocaleCompleteness[] $localeCompletenesses
     * @param array $channelLabels
     */
    public function __construct(string $channelCode, int $numberOfCompleteProducts, int $numberTotalOfProducts, array $localeCompletenesses, array $channelLabels = [])
    {
        $this->channelCode = $channelCode;
        $this->channelLabels = $channelLabels;
        $this->numberOfCompleteProducts = $numberOfCompleteProducts;
        $this->numberTotalOfProducts = $numberTotalOfProducts;
        $this->localeCompletenesses = $localeCompletenesses;
    }

    /**
     * @return string
     */
    public function channel(): string
    {
        return $this->channelCode;
    }

    /**
     * @return int
     */
    public function numberOfCompleteProducts(): int
    {
        return $this->numberOfCompleteProducts;
    }

    /**
     * @return int
     */
    public function numberTotalOfProducts(): int
    {
        return $this->numberTotalOfProducts;
    }

    /**
     * @return array
     */
    public function localeCompletenesses(): array
    {
        return $this->localeCompletenesses;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $locales = [];
        foreach ($this->localeCompletenesses as $localeCompleteness) {
            $locales = array_merge($locales, $localeCompleteness->toArray());
        }

        return [
            'labels' => $this->channelLabels,
            'total' => $this->numberTotalOfProducts,
            'complete' => $this->numberOfCompleteProducts,
            'locales' => $locales
        ];
    }
}
