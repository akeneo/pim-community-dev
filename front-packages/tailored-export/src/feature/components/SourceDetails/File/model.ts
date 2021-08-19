import {uuid} from 'akeneo-design-system';
import {ChannelReference, LocaleReference} from '@akeneo-pim-community/shared';
import {Attribute, Source} from '../../../models';
import {DefaultValueOperation, isDefaultValueOperation} from '../common';

type FileSelection = {
  type: 'path' | 'key' | 'name';
};

const isFileSelection = (selection: any): selection is FileSelection =>
  'type' in selection && ('path' === selection.type || 'key' === selection.type || 'name' === selection.type);

type FileOperations = {
  default_value?: DefaultValueOperation;
};

type FileSource = {
  uuid: string;
  code: string;
  type: 'attribute';
  locale: LocaleReference;
  channel: ChannelReference;
  operations: FileOperations;
  selection: FileSelection;
};

const getDefaultFileSource = (
  attribute: Attribute,
  channel: ChannelReference,
  locale: LocaleReference
): FileSource => ({
  uuid: uuid(),
  code: attribute.code,
  type: 'attribute',
  locale,
  channel,
  operations: {},
  selection: {type: 'path'},
});

const isFileOperations = (operations: Object): operations is FileOperations =>
  Object.entries(operations).every(([type, operation]) => {
    switch (type) {
      case 'default_value':
        return isDefaultValueOperation(operation);
      default:
        return false;
    }
  });

const isFileSource = (source: Source): source is FileSource =>
  isFileSelection(source.selection) && isFileOperations(source.operations);

export {getDefaultFileSource, isFileSource};
export type {FileSelection, FileSource};
