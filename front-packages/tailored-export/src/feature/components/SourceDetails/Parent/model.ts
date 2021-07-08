import {uuid} from 'akeneo-design-system';
import {ChannelCode, LocaleCode} from '@akeneo-pim-community/shared';
import {Source} from '../../../models';

type ParentSelection =
  | {
      type: 'code';
    }
  | {
      type: 'label';
      channel: ChannelCode;
      locale: LocaleCode;
    };

type ParentSource = {
  uuid: string;
  code: 'parent';
  type: 'property';
  locale: null;
  channel: null;
  operations: {};
  selection: ParentSelection;
};

const getDefaultParentSource = (): ParentSource => ({
  uuid: uuid(),
  code: 'parent',
  type: 'property',
  locale: null,
  channel: null,
  operations: {},
  selection: {type: 'code'},
});

const isParentSource = (source: Source): source is ParentSource => 'parent' === source.code;

export {getDefaultParentSource, isParentSource};
export type {ParentSource, ParentSelection};
