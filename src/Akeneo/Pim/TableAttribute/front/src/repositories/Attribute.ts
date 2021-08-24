import {Router} from '@akeneo-pim-community/shared';
import {Attribute} from '../models/Attribute';
import {fetchAttribute} from '../fetchers/AttributeFetcher';

const attributeCalls: {[attributeCode: string]: Promise<Attribute>} = {};
const attributeCache: {[attributeCode: string]: Attribute} = {};

const getAttribute: (router: Router, attributeCode: string) => Promise<Attribute> = async (router, attributeCode) => {
  if (!(attributeCode in attributeCache)) {
    if (!(attributeCode in attributeCalls)) {
      attributeCalls[attributeCode] = fetchAttribute(router, attributeCode);
    }
    attributeCache[attributeCode] = (await attributeCalls[attributeCode]) ?? null;
  }
  return attributeCache[attributeCode];
};

export {getAttribute};
