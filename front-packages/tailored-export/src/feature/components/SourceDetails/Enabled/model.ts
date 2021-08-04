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
    replacement: BooleanReplacementOperation;
  };
  selection: {type: 'code'};
};

const getDefaultEnabledSource = (): EnabledSource => ({
  uuid: uuid(),
  code: 'enabled',
  type: 'property',
  locale: null,
  channel: null,
  operations: {
    replacement: {
      type: 'replacement',
      mapping: {
        true: '1',
        false: '0',
      },
    },
  },
  selection: {type: 'code'},
});

const isEnabledSource = (source: Source): source is EnabledSource =>
  'object' === typeof source && null !== source && 'enabled' === source.code && 'replacement' in source.operations;

export {getDefaultEnabledSource, isEnabledSource};
export type {EnabledSource};
