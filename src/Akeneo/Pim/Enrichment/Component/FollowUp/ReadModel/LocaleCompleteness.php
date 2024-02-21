<?php
declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\FollowUp\ReadModel;

/**
 * LocaleCompleteness class represents the completeness for a locale to show it in the dashboard
 *
 * @author    Elodie Raposo <elodie.raposo@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LocaleCompleteness
{
    /** @var string */
    private $locale;

    /** @var int */
    private $numberOfCompleteProducts;

    /**
     * @param string $locale
     * @param int $numberOfCompleteProducts
     */
    public function __construct(string $locale, int $numberOfCompleteProducts)
    {
        $this->locale = $locale;
        $this->numberOfCompleteProducts = $numberOfCompleteProducts;
    }

    /**
     * @return string
     */
    public function locale(): string
    {
        return $this->locale;
    }

    /**
     * @return int
     */
    public function numberOfCompleteProducts(): int
    {
        return $this->numberOfCompleteProducts;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
          $this->locale => $this->numberOfCompleteProducts
        ];
    }
}
