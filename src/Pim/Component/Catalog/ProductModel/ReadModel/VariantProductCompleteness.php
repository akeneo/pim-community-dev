<?php
declare(strict_types=1);

namespace Pim\Component\Catalog\ProductModel\ReadModel;

/**
 * Represent data regarding the variant product completenesses to build the ratio on the PMEF.
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VariantProductCompleteness
{
    /** @var array */
    private $completenesses;

    /**
     * @param array $completenesses
     */
    public function __construct(array $completenesses)
    {
        $this->completenesses = $completenesses;
    }

    /**
     * Return the number of variant product
     *
     * @return array
     */
    public function normalizedCompletenesses(): array
    {
        $completenesses = [];
        $completenesses['completenesses'] =  $this->parsedFlatCompletenesses();
        $completenesses['total'] = $this->numberOfProduct();

        return $completenesses;
    }

    /**
     * Return the number of variant product depending on the channel and the locale
     *
     * @param string $channel
     * @param string $locale
     *
     * @return mixed
     */
    public function ratio(string $channel, string $locale): string
    {
        $completenesses =  $this->parsedFlatCompletenesses();

        if (!isset($completenesses[$channel][$locale])) {
            throw new \InvalidArgumentException(
                sprintf('There is no completeness for the channel %s and the locale %s', $channel, $locale)
            );
        }

        return sprintf('%d/%d', $completenesses[$channel][$locale], $this->numberOfProduct());
    }

    /**
     * Return number of product variant
     *
     * @return int
     */
    private function numberOfProduct(): int
    {
        return count(
            array_unique(
                array_column($this->completenesses, 'pr')
            )
        );
    }

    /**
     * Return the structured variant product completenesses
     *
     * channel
     *      locale: number of product
     *      locale: number of product
     *
     * @return array
     */
    private function parsedFlatCompletenesses(): array
    {
        $completenesses = [];
        foreach ($this->completenesses as $completeness) {
            $locale = $completeness['lo'];
            $channel = $completeness['ch'];
            if (!isset($completenesses[$channel][$locale])) {
                $completenesses[$channel][$locale] = 0;
            }

            $completenesses[$channel][$locale] = $completenesses[$channel][$locale] + $completeness['co'];
        }

        return $completenesses;
    }
}
