import validateAgainstSchema from 'akeneoassetmanager/infrastructure/tools/validator';
import {BackendAssetFamily} from 'akeneoassetmanager/infrastructure/model/backend-asset-family';

const schema = require('akeneoassetmanager/infrastructure/model/backend-asset-family.schema.json');

export default (data: any): BackendAssetFamily => {
  return validateAgainstSchema(data, schema);
};
