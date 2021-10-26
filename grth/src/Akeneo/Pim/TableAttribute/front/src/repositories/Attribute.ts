import {Router} from '@akeneo-pim-community/shared';
import {Attribute, AttributeCode, AttributeType} from '../models';
import {AttributeFetcher} from '../fetchers';

const attributeCalls: {[attributeCode: string]: Promise<Attribute>} = {};
const attributeCache: {[attributeCode: string]: Attribute} = {};

const getAttribute: (router: Router, attributeCode: AttributeCode) => Promise<Attribute> = async (
  router,
  attributeCode
) => {
  if (!(attributeCode in attributeCache)) {
    if (!(attributeCode in attributeCalls)) {
      attributeCalls[attributeCode] = AttributeFetcher.fetch(router, attributeCode);
    }
    attributeCache[attributeCode] = (await attributeCalls[attributeCode]) ?? null;
  }
  return attributeCache[attributeCode];
};

const findAllByTypes: (router: Router, attributeTypes: AttributeType[]) => Promise<Attribute[]> = async (
  router,
  attributeTypes
) => {
  // TODO Add Cache
  return AttributeFetcher.fetchByTypes(router, attributeTypes)
};

const AttributeRepository = {
  find: getAttribute,
  findAllByTypes: findAllByTypes,
};

export {AttributeRepository};
