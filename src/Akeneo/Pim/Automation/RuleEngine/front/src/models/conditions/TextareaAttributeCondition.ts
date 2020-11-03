import {Router} from '../../dependenciesTools';
import {getAttributeByIdentifier} from '../../repositories/AttributeRepository';
import {TextareaAttributeConditionLine} from '../../pages/EditRules/components/conditions/TextareaAttributeConditionLine';
import {Operator} from '../Operator';
import {ConditionFactory} from './';
import {ConditionModuleGuesser} from './ConditionModuleGuesser';
import {AttributeType} from '../Attribute';

const TYPE = AttributeType.TEXTAREA;

const TextareaAttributeOperators = [
  Operator.EQUALS,
  Operator.NOT_EQUAL,
  Operator.CONTAINS,
  Operator.STARTS_WITH,
  Operator.DOES_NOT_CONTAIN,
  Operator.IS_EMPTY,
  Operator.IS_NOT_EMPTY,
];

type TextareaAttributeCondition = {
  scope?: string;
  field: string;
  operator: Operator;
  value?: string;
  locale?: string;
};

const createTextareaAttributeCondition: ConditionFactory = async (
  fieldCode: string,
  router: Router
): Promise<TextareaAttributeCondition | null> => {
  const attribute = await getAttributeByIdentifier(fieldCode, router);
  if (null === attribute || attribute.type !== TYPE) {
    return null;
  }

  return {
    field: fieldCode,
    operator: Operator.IS_EMPTY,
  };
};

const getTextareaAttributeConditionModule: ConditionModuleGuesser = async (
  json,
  router
) => {
  if (typeof json.field !== 'string') {
    return null;
  }

  if (
    typeof json.operator !== 'string' ||
    !TextareaAttributeOperators.includes(json.operator)
  ) {
    return null;
  }

  const attribute = await getAttributeByIdentifier(json.field, router);
  if (null === attribute || attribute.type !== TYPE) {
    return null;
  }

  return TextareaAttributeConditionLine;
};

export {
  TextareaAttributeOperators,
  TextareaAttributeCondition,
  getTextareaAttributeConditionModule,
  createTextareaAttributeCondition,
};
