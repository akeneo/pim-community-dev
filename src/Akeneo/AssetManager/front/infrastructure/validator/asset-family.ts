import validateAgainstSchema from 'akeneoassetmanager/infrastructure/tools/validator';
import {BackendAssetFamily} from 'akeneoassetmanager/infrastructure/model/asset-family';

const schema = require('akeneoassetmanager/infrastructure/model/asset-family.schema.json');

export default (data: any): BackendAssetFamily => {
  return validateAgainstSchema<BackendAssetFamily>(data, schema);
};
