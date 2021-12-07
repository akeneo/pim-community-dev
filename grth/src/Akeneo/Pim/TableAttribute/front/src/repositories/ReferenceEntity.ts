import {Router} from '@akeneo-pim-community/shared';
import {ReferenceEntity} from '../models';
import {ReferenceEntityFetcher} from '../fetchers';

let referenceEntitiesCache: ReferenceEntity[] | undefined;

const all = async (router: Router): Promise<ReferenceEntity[]> => {
  if (typeof referenceEntitiesCache === 'undefined') {
    referenceEntitiesCache = await ReferenceEntityFetcher.fetchAll(router);
  }
  return Promise.resolve(referenceEntitiesCache);
};

const ReferenceEntityRepository = {
  all,
};

export {ReferenceEntityRepository};
