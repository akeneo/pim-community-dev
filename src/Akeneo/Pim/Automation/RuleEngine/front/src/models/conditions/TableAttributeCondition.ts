import {TableAttributeConditionLine} from '../../pages/EditRules/components/conditions/TableAttributeConditionLine';
import {Operator} from '../Operator';
import {ConditionFactory} from './Condition';
import {ConditionModuleGuesser} from './ConditionModuleGuesser';
import {AttributeType} from '../Attribute';
import {getAttributeByIdentifier} from '../../repositories/AttributeRepository';

type TableAttributeCondition = {
  field: string;
  operator?: Operator;
  value: {
    value?: any;
    row?: string;
    column?: string;
  };
  locale?: string;
  scope?: string;
};

const createTableAttributeCondition: ConditionFactory = async (
  fieldCode,
  router
) => {
  const attribute = await getAttributeByIdentifier(fieldCode, router);
  if (null === attribute || attribute.type !== AttributeType.TABLE) {
    return null;
  }

  return {
    field: fieldCode,
    value: {},
  } as TableAttributeCondition;
};

const getTableAttributeConditionModule: ConditionModuleGuesser = async (
  json,
  router
) => {
  if (typeof json.field !== 'string') {
    return null;
  }

  if (typeof json.value !== 'object') {
    return null;
  }

  const attribute = await getAttributeByIdentifier(json.field, router);
  if (null === attribute || attribute.type !== AttributeType.TABLE) {
    return null;
  }

  return TableAttributeConditionLine;
};

export {
  TableAttributeCondition,
  getTableAttributeConditionModule,
  createTableAttributeCondition,
};
