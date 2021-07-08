import {uuid} from 'akeneo-design-system';
import {ChannelReference, LocaleReference} from '@akeneo-pim-community/shared';
import {Source, Attribute} from '../../../models';
import {CodeLabelSelection, isCodeLabelSelection} from '../common/CodeLabelSelector';

type ReferenceEntitySource = {
  uuid: string;
  code: string;
  type: 'attribute';
  locale: LocaleReference;
  channel: ChannelReference;
  operations: {};
  selection: CodeLabelSelection;
};

const getDefaultReferenceEntitySource = (
  attribute: Attribute,
  channel: ChannelReference,
  locale: LocaleReference
): ReferenceEntitySource => ({
  uuid: uuid(),
  code: attribute.code,
  type: 'attribute',
  locale,
  channel,
  operations: {},
  selection: {type: 'code'},
});

const isReferenceEntitySource = (source: Source): source is ReferenceEntitySource =>
  isCodeLabelSelection(source.selection);

export {isReferenceEntitySource, getDefaultReferenceEntitySource};
export type {ReferenceEntitySource};
