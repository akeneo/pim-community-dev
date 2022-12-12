import {Family} from '../models';
import {FetcherValue} from '../contexts';

let cacheFamilies: {[familyCode: string]: Family};

export const getAllFamilies = async (fetcher: FetcherValue): Promise<{[familyCode: string]: Family}> => {
  if (!cacheFamilies) {
    cacheFamilies = await fetcher.family.fetchAllFamilies();
  }

  return cacheFamilies;
};
