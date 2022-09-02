import {Router} from '@akeneo-pim-community/shared';
import {ReferenceEntity, ReferenceEntityIdentifierOrCode} from '../models';
import {ReferenceEntityFetcher} from '../fetchers';

let referenceEntitiesCache: ReferenceEntity[] | undefined;

const all = async (router: Router): Promise<ReferenceEntity[]> => {
  if (typeof referenceEntitiesCache === 'undefined') {
    referenceEntitiesCache = await ReferenceEntityFetcher.fetchAll(router);
  }
  return Promise.resolve(referenceEntitiesCache);
};

const findByIdentifier = async (
  router: Router,
  referenceEntityIdentifier: ReferenceEntityIdentifierOrCode
): Promise<ReferenceEntity | undefined> => {
  const referenceEntities = await all(router);
  return referenceEntities.find(referenceEntity => referenceEntity.identifier === referenceEntityIdentifier);
};

const ReferenceEntityRepository = {
  all,
  findByIdentifier,
};

export {ReferenceEntityRepository};
