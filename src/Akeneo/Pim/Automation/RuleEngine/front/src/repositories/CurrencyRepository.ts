import { fetchAllCurrencies } from "../fetch/CurrencyFetcher";
import { Router } from "../dependenciesTools";
import { Currency } from "../models/Currency";

type IndexedCurrencies = { [currencyCode: string]: Currency };
let cachedCurrencies: IndexedCurrencies;

const getAllCurrencies: (router: Router) => Promise<IndexedCurrencies> = async (router) => {
  if (!cachedCurrencies) {
    cachedCurrencies = await fetchAllCurrencies(router);
  }

  return cachedCurrencies;
}

export { getAllCurrencies, IndexedCurrencies };
