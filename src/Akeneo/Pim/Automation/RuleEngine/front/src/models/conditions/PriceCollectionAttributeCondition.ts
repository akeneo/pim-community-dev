import {Operator} from '../Operator';
import {ConditionFactory} from './Condition';
import {ConditionModuleGuesser} from './ConditionModuleGuesser';
import {AttributeType} from '../Attribute';
import {
  createAttributeCondition,
  getAttributeConditionModule,
} from './AbstractAttributeCondition';
import {PriceCollectionAttributeConditionLine} from '../../pages/EditRules/components/conditions/PriceCollectionAttributeConditionLine';

const PriceCollectionAttributeOperators = [
  Operator.EQUALS,
  Operator.NOT_EQUAL,
  Operator.LOWER_THAN,
  Operator.LOWER_OR_EQUAL_THAN,
  Operator.GREATER_THAN,
  Operator.GREATER_OR_EQUAL_THAN,
  Operator.IS_EMPTY,
  Operator.IS_NOT_EMPTY,
];

type PriceCollectionAttributeCondition = {
  field: string;
  scope?: string;
  locale?: string;
  value?: {
    amount: number;
    currency: string;
  };
  operator: Operator;
};

const createPriceCollectionAttributeCondition: ConditionFactory = async (
  fieldCode,
  router
) => {
  return createAttributeCondition(
    fieldCode,
    router,
    [AttributeType.PRICE_COLLECTION],
    Operator.IS_EMPTY,
    {
      amount: '',
      currency: '',
    }
  );
};

const getPriceCollectionAttributeConditionModule: ConditionModuleGuesser = async (
  json,
  router
) => {
  return getAttributeConditionModule(
    json,
    router,
    PriceCollectionAttributeOperators,
    [AttributeType.PRICE_COLLECTION],
    PriceCollectionAttributeConditionLine
  );
};

export {
  PriceCollectionAttributeOperators,
  PriceCollectionAttributeCondition,
  getPriceCollectionAttributeConditionModule,
  createPriceCollectionAttributeCondition,
};
