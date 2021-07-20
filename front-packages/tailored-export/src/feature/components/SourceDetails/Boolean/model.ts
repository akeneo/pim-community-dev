import {uuid} from 'akeneo-design-system';
import {ChannelReference, LocaleReference} from '@akeneo-pim-community/shared';
import {Attribute} from '../../../models';

type BooleanSource = {
  uuid: string;
  code: string;
  type: 'attribute';
  locale: LocaleReference;
  channel: ChannelReference;
  operations: {};
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

export type {BooleanSource};
export {getDefaultBooleanSource};
