import {uuid} from 'akeneo-design-system';
import {ChannelReference, LocaleReference} from '@akeneo-pim-community/shared';
import {Attribute} from '../../../models';

type NumberSource = {
  uuid: string;
  code: string;
  type: 'attribute';
  locale: LocaleReference;
  channel: ChannelReference;
  operations: {};
  selection: {type: 'code'};
};

const getDefaultNumberSource = (
  attribute: Attribute,
  channel: ChannelReference,
  locale: LocaleReference
): NumberSource => ({
  uuid: uuid(),
  code: attribute.code,
  type: 'attribute',
  locale,
  channel,
  operations: {},
  selection: {type: 'code'},
});

export type {NumberSource};
export {getDefaultNumberSource};
