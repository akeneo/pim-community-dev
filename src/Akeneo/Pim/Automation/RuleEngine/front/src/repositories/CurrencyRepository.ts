import {fetchAllCurrencies} from '../fetch/CurrencyFetcher';
import {Router} from '../dependenciesTools';
import {Currency, CurrencyCode} from '../models/Currency';

type IndexedCurrencies = {[currencyCode: string]: Currency};
let cachedCurrencies: IndexedCurrencies | undefined;

const clearCurrencyRepositoryCache = () => {
  cachedCurrencies = undefined;
};

const getAllCurrencies: (
  router: Router
) => Promise<IndexedCurrencies> = async router => {
  if (!cachedCurrencies) {
    cachedCurrencies = await fetchAllCurrencies(router);
  }

  return cachedCurrencies;
};

const getCurrenciesByCode: (
  currencyCodes: CurrencyCode[],
  router: Router
) => Promise<IndexedCurrencies> = async (currencyCodes, router) => {
  const currencies = await getAllCurrencies(router);

  return currencyCodes.reduce(
    (previousValue, currencyCode) => ({
      ...previousValue,
      [currencyCode]: currencies[currencyCode],
    }),
    {}
  );
};

export {
  getAllCurrencies,
  IndexedCurrencies,
  getCurrenciesByCode,
  clearCurrencyRepositoryCache,
};
