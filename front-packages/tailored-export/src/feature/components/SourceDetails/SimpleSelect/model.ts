import {uuid} from 'akeneo-design-system';
import {LocaleReference, ChannelReference} from '@akeneo-pim-community/shared';
import {Attribute, Source} from '../../../models';
import {CodeLabelSelection, isCodeLabelSelection} from '../common/CodeLabelSelector';

type SimpleSelectSource = {
  uuid: string;
  code: string;
  type: 'attribute';
  locale: LocaleReference;
  channel: ChannelReference;
  operations: {};
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
  selection: {type: 'code'},
});

const isSimpleSelectSource = (source: Source): source is SimpleSelectSource => isCodeLabelSelection(source.selection);

export {getDefaultSimpleSelectSource, isSimpleSelectSource};
export type {SimpleSelectSource};
