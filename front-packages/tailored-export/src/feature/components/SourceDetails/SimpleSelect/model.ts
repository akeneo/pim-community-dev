import {uuid} from 'akeneo-design-system';
import {LocaleReference, ChannelReference} from '@akeneo-pim-community/shared';
import {Attribute, Source} from '../../../models';
import {
  CodeLabelSelection,
  getDefaultCodeLabelSelection,
  isCodeLabelSelection,
  DefaultValueOperation,
  isDefaultValueOperation,
} from '../common';

type SimpleSelectOperations = {
  default_value?: DefaultValueOperation;
};

type SimpleSelectSource = {
  uuid: string;
  code: string;
  type: 'attribute';
  locale: LocaleReference;
  channel: ChannelReference;
  operations: SimpleSelectOperations;
  selection: CodeLabelSelection;
};

const getDefaultSimpleSelectSource = (
  attribute: Attribute,
  channel: ChannelReference,
  locale: LocaleReference
): SimpleSelectSource => ({
  uuid: uuid(),
  code: attribute.code,
  type: 'attribute',
  locale,
  channel,
  operations: {},
  selection: getDefaultCodeLabelSelection(),
});

const isSimpleSelectOperations = (operations: Object): operations is SimpleSelectOperations =>
  Object.entries(operations).every(([type, operation]) => {
    switch (type) {
      case 'default_value':
        return isDefaultValueOperation(operation);
      default:
        return false;
    }
  });

const isSimpleSelectSource = (source: Source): source is SimpleSelectSource =>
  isCodeLabelSelection(source.selection) && isSimpleSelectOperations(source.operations);

export {getDefaultSimpleSelectSource, isSimpleSelectSource};
export type {SimpleSelectSource};
