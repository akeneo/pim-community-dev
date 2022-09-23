import {uuid} from 'akeneo-design-system';
import {ChannelReference, LocaleReference} from '@akeneo-pim-community/shared';
import {Attribute, Source} from '../../../models';
import {
  DefaultValueOperation,
  isDefaultValueOperation,
  ExtractOperation,
  isExtractOperation,
  SplitOperation,
  isSplitOperation,
} from '../common';
import {CleanHTMLTagsOperation, isCleanHTMLTagsOperation, CLEAN_HTML_TAGS_OPERATION} from './CleanHTMLTags';

type TextOperations = {
  default_value?: DefaultValueOperation;
  clean_html_tags?: CleanHTMLTagsOperation;
  extract?: ExtractOperation;
  split?: SplitOperation;
};

type TextSource = {
  uuid: string;
  code: string;
  type: 'attribute';
  locale: LocaleReference;
  channel: ChannelReference;
  operations: TextOperations;
  selection: {type: 'code'};
};

const getDefaultTextSource = (
  attribute: Attribute,
  channel: ChannelReference,
  locale: LocaleReference
): TextSource => ({
  uuid: uuid(),
  code: attribute.code,
  type: 'attribute',
  locale,
  channel,
  operations: {},
  selection: {type: 'code'},
});

const isTextOperations = (operations: Object): operations is TextOperations => {
  return Object.entries(operations).every(([type, operation]) => {
    switch (type) {
      case 'default_value':
        return isDefaultValueOperation(operation);
      case 'extract':
        return isExtractOperation(operation);
      case 'split':
        return isSplitOperation(operation);
      case CLEAN_HTML_TAGS_OPERATION:
        return isCleanHTMLTagsOperation(operation);
      default:
        return false;
    }
  });
};

const isTextSource = (source: Source): source is TextSource =>
  'type' in source.selection && 'code' === source.selection.type && isTextOperations(source.operations);

export type {TextSource};
export {getDefaultTextSource, isTextSource};
