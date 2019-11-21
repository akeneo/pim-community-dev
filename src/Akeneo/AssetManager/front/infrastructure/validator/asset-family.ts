import validateAgainstSchema from 'akeneoassetmanager/infrastructure/tools/validator';
import {AssetFamily} from 'akeneoassetmanager/infrastructure/model/asset-family';

const schema = require('akeneoassetmanager/infrastructure/model/asset-family.schema.json');

export default (data: any): AssetFamily => {
  return validateAgainstSchema(data, schema);
};
