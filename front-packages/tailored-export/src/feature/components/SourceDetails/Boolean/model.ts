import {uuid} from 'akeneo-design-system';
import {ChannelReference, LocaleReference} from '@akeneo-pim-community/shared';
import {Attribute, Source} from '../../../models';
import {BooleanReplacementOperation, isBooleanReplacementOperation} from '../common/BooleanReplacement';
import { DefaultValueOperation, isDefaultValueOperation } from '../common/DefaultValue';

type BooleanOperations = {
  replacement?: BooleanReplacementOperation;
  default_value?: DefaultValueOperation;
};

type BooleanSource = {
  uuid: string;
  code: string;
  type: 'attribute';
  locale: LocaleReference;
  channel: ChannelReference;
  operations: BooleanOperations;
  selection: {type: 'code'};
};

const getDefaultBooleanSource = (
  attribute: Attribute,
  channel: ChannelReference,
  locale: LocaleReference
): BooleanSource => ({
  uuid: uuid(),
  code: attribute.code,
  type: 'attribute',
  locale,
  channel,
  operations: {},
  selection: {type: 'code'},
});

const isBooleanOperations = (operations: any): operations is BooleanOperations => {
  const isReplacementValid = 'replacement' in operations ? isBooleanReplacementOperation(operations.replacement) : true;
  const isDefaultValueValid = 'default_value' in operations ? isDefaultValueOperation(operations.default_value) : true;

  return isReplacementValid && isDefaultValueValid;
}
  

const isBooleanSource = (source: Source): source is BooleanSource =>
  'object' === typeof source &&
  null !== source &&
  'attribute' === source.type &&
  'type' in source.selection &&
  'code' === source.selection.type &&
  isBooleanOperations(source.operations);

export type {BooleanSource};
export {getDefaultBooleanSource, isBooleanSource, isBooleanOperations};
