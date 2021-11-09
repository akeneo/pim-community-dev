import {Operator} from '../Operator';
import {ConditionFactory} from './Condition';
import {AssetCollectionAttributeConditionLine} from '../../pages/EditRules/components/conditions/AssetCollectionAttributeConditionLine';
import {ConditionModuleGuesser} from './ConditionModuleGuesser';
import {AttributeType} from '../Attribute';
import {
  createAttributeCondition,
  getAttributeConditionModule,
} from './AbstractAttributeCondition';

const AssetCollectionAttributeOperators = [
  Operator.IN_LIST,
  Operator.NOT_IN_LIST,
  Operator.IS_EMPTY,
  Operator.IS_NOT_EMPTY,
];

type AssetCollectionAttributeCondition = {
  scope?: string;
  field: string;
  operator: Operator;
  value?: string[];
  locale?: string;
};

const createAssetCollectionAttributeCondition: ConditionFactory = async (
  fieldCode,
  router
) => {
  return createAttributeCondition(
    fieldCode,
    router,
    [AttributeType.ASSET_COLLECTION],
    Operator.IS_EMPTY
  );
};

const getAssetCollectionAttributeConditionModule: ConditionModuleGuesser = async (
  json,
  router
) => {
  return getAttributeConditionModule(
    json,
    router,
    AssetCollectionAttributeOperators,
    [AttributeType.ASSET_COLLECTION],
    AssetCollectionAttributeConditionLine
  );
};

export {
  AssetCollectionAttributeOperators,
  AssetCollectionAttributeCondition,
  getAssetCollectionAttributeConditionModule,
  createAssetCollectionAttributeCondition,
};
