import {uuid} from 'akeneo-design-system';
import {BooleanReplacementOperation} from '../common/BooleanReplacement';
import {Source} from '../../../models';

type EnabledSource = {
  uuid: string;
  code: 'enabled';
  type: 'property';
  locale: null;
  channel: null;
  operations: {
    replacement?: BooleanReplacementOperation;
  };
  selection: {type: 'code'};
};

const getDefaultEnabledSource = (): EnabledSource => ({
  uuid: uuid(),
  code: 'enabled',
  type: 'property',
  locale: null,
  channel: null,
  operations: {},
  selection: {type: 'code'},
});

const isEnabledSource = (source: Source): source is EnabledSource =>
  'object' === typeof source &&
  null !== source &&
  'enabled' === source.code &&
  'type' in source.selection &&
  'code' === source.selection.type;

export {getDefaultEnabledSource, isEnabledSource};
export type {EnabledSource};
