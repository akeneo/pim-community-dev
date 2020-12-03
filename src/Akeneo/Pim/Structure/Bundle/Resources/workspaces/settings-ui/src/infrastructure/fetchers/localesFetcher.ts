import {Locale} from '../../models';

const FetcherRegistry = require('pim/fetcher-registry');

const fetchAllLocales = async (): Promise<Locale[]> => {
  try {
    return FetcherRegistry.getFetcher('locale').fetchAll();
  } catch (error) {
    console.error(error);
    return Promise.resolve([]);
  }
};
const fetchActivatedLocales = async (): Promise<Locale[]> => {
  try {
    return FetcherRegistry.getFetcher('locale').fetchActivated();
  } catch (error) {
    console.error(error);
    return Promise.resolve([]);
  }
};

export {fetchAllLocales, fetchActivatedLocales};
