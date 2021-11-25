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

const getDefaultParentSelection = (): ParentSelection => ({type: 'code'});

const isDefaultParentSelection = (selection?: ParentSelection): boolean => 'code' === selection?.type;

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
  selection: getDefaultParentSelection(),
});

const isParentSource = (source: Source): source is ParentSource => 'parent' === source.code;

export {getDefaultParentSource, isDefaultParentSelection, isParentSource};
export type {ParentSource, ParentSelection};
