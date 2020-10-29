import {Operator} from '../Operator';
import {ConditionFactory} from './Condition';
import {
  createAttributeCondition,
  getAttributeConditionModule,
} from './AbstractAttributeCondition';
import {AttributeType} from '../Attribute';
import {ConditionModuleGuesser} from './ConditionModuleGuesser';
import {PictureAttributeConditionLine} from '../../pages/EditRules/components/conditions/PictureAttributeConditionLine';

const PictureAttributeOperators = [
  Operator.EQUALS,
  Operator.NOT_EQUAL,
  Operator.CONTAINS,
  Operator.DOES_NOT_CONTAIN,
  Operator.STARTS_WITH,
  Operator.IS_EMPTY,
  Operator.IS_NOT_EMPTY,
];

type PictureAttributeCondition = {
  scope?: string;
  field: string;
  operator: Operator;
  value?: string;
  locale?: string;
};

const createPictureAttributeCondition: ConditionFactory = async (
  fieldCode,
  router
) => {
  return createAttributeCondition(
    fieldCode,
    router,
    [AttributeType.IMAGE],
    Operator.IS_EMPTY
  );
};

const getPictureAttributeConditionModule: ConditionModuleGuesser = async (
  json,
  router
) => {
  return getAttributeConditionModule(
    json,
    router,
    PictureAttributeOperators,
    [AttributeType.IMAGE],
    PictureAttributeConditionLine
  );
};

export {
  PictureAttributeOperators,
  PictureAttributeCondition,
  getPictureAttributeConditionModule,
  createPictureAttributeCondition,
};
