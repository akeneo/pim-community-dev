import {Locale} from '../../models';

const FetcherRegistry = require('pim/fetcher-registry');

const fetchAllLocales = async (): Promise<Locale[]> => {
  try {
    return FetcherRegistry.getFetcher('locale').fetchAll();
  } catch (error) {
    console.error(error);
    return [];
  }
};
const fetchActivatedLocales = async (): Promise<Locale[]> => {
  try {
    return FetcherRegistry.getFetcher('locale').fetchActivated();
  } catch (error) {
    console.error(error);
    return [];
  }
};

export {fetchAllLocales, fetchActivatedLocales};
