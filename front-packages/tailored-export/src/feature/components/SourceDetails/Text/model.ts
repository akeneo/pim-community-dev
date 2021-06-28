import {uuid} from 'akeneo-design-system';
import {ChannelReference, LocaleReference} from '@akeneo-pim-community/shared';
import {Attribute} from '../../../models';

type TextSource = {
  uuid: string;
  code: string;
  type: 'attribute';
  locale: LocaleReference;
  channel: ChannelReference;
  operations: {};
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

export type {TextSource};
export {getDefaultTextSource};
