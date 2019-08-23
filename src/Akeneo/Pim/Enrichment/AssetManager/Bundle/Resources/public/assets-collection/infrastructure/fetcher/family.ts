import promisify from 'akeneoassetmanager/tools/promisify';
import {Family, FamilyCode} from 'akeneopimenrichmentassetmanager/enrich/domain/model/product';
const fetcherRegistry = require('pim/fetcher-registry');

export const fetchFamily = async (familyCode: FamilyCode): Promise<Family> => {
  return promisify(fetcherRegistry.getFetcher('family').fetch(familyCode));
};
