import { Operator } from '../Operator';
import { ConditionFactory } from './Condition';
import {
  createAttributeCondition,
  getAttributeConditionModule,
} from './AbstractAttributeCondition';
import { AttributeType } from '../Attribute';
import { ConditionModuleGuesser } from './ConditionModuleGuesser';
import { FileAttributeConditionLine } from '../../pages/EditRules/components/conditions/FileAttributeConditionLine';

const FileAttributeOperators = [
  Operator.EQUALS,
  Operator.NOT_EQUAL,
  Operator.CONTAINS,
  Operator.DOES_NOT_CONTAIN,
  Operator.STARTS_WITH,
  Operator.IS_EMPTY,
  Operator.IS_NOT_EMPTY,
];

type FileAttributeCondition = {
  scope?: string;
  field: string;
  operator: Operator;
  value?: string;
  locale?: string;
};

const createFileAttributeCondition: ConditionFactory = async (
  fieldCode,
  router
) => {
  return createAttributeCondition(
    fieldCode,
    router,
    [AttributeType.FILE],
    Operator.IS_EMPTY
  );
};

const getFileAttributeConditionModule: ConditionModuleGuesser = async (
  json,
  router
) => {
  return getAttributeConditionModule(
    json,
    router,
    FileAttributeOperators,
    [AttributeType.FILE],
    FileAttributeConditionLine
  );
};

export {
  FileAttributeOperators,
  FileAttributeCondition,
  getFileAttributeConditionModule,
  createFileAttributeCondition,
};
