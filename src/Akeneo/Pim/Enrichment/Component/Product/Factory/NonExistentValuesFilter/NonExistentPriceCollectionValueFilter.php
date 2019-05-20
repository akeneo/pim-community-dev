<?php
declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter;

use Akeneo\Channel\Component\Query\FindActivatedCurrenciesInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class NonExistentPriceCollectionValueFilter implements NonExistentValuesFilter
{
    /** @var FindActivatedCurrenciesInterface */
    private $findActivatedCurrencies;

    public function __construct(FindActivatedCurrenciesInterface $findActivatedCurrencies)
    {
        $this->findActivatedCurrencies = $findActivatedCurrencies;
    }

    public function filter(OnGoingFilteredRawValues $onGoingFilteredRawValues): OnGoingFilteredRawValues
    {
        $priceCollectionValues = $onGoingFilteredRawValues->notFilteredValuesOfTypes(AttributeTypes::PRICE_COLLECTION);

        if (empty($priceCollectionValues)) {
            return $onGoingFilteredRawValues;
        }

        return $onGoingFilteredRawValues
            ->addFilteredValuesIndexedByType(
                $this->getExistingPriceCollectionValues($priceCollectionValues)
            );
    }

    /**
     * This method is a little bit complicated, just keep in mind that it returns values of type prices without the non activated locales
     */
    private function getExistingPriceCollectionValues(array $priceCollectionValues): array
    {
        $filteredValues = [];

        foreach ($priceCollectionValues as $attributeCode => $productListData) {
            foreach ($productListData as $productData) {
                $priceCollectionValues = [];
                foreach ($productData['values'] as $channel => $valuesIndexedByLocale) {
                    //This query is cached do not worry of calling it multiple times
                    $activatedCurrencies = [];
                    if ($channel === null || $channel === '<all_channels>') {
                        $activatedCurrencies = $this->findActivatedCurrencies->forAllChannels();
                    } else {
                        $activatedCurrencies = $this->findActivatedCurrencies->forChannel($channel);
                    }

                    foreach ($valuesIndexedByLocale as $locale => $value) {
                        $amountByCurrency = [];
                        foreach ($value as $price) {
                            if (isset($price['amount']) && isset($price['currency'])) {
                                if (in_array($price['currency'], $activatedCurrencies)) {
                                    //Only the last price by currency is kept
                                    $amountByCurrency[$price['currency']] = $price['amount'];
                                }
                            }
                        }
                        $formattedPriceData = [];
                        foreach ($amountByCurrency as $currency => $amount) {
                            $formattedPriceData[] = ['currency' => $currency, 'amount' => $amount];
                        }
                        if (!empty($formattedPriceData)) {
                            $priceCollectionValues[$channel][$locale] = $formattedPriceData;
                        }
                    }
                }
                if ($priceCollectionValues !== []) {
                    $filteredValues[AttributeTypes::PRICE_COLLECTION][$attributeCode][] = [
                        'identifier' => $productData['identifier'],
                        'values' => $priceCollectionValues,
                        'properties' => $productData['properties'] ?? []
                    ];
                }
            }
        }

        return $filteredValues;
    }
}
