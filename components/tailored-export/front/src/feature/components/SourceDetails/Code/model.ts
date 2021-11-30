import {uuid} from 'akeneo-design-system';
import {Source} from '../../../models';

type CodeSource = {
  uuid: string;
  code: 'code';
  type: 'property';
  locale: null;
  channel: null;
  operations: {};
  selection: {type: 'code'};
};

const getDefaultCodeSource = (): CodeSource => ({
  uuid: uuid(),
  code: 'code',
  type: 'property',
  locale: null,
  channel: null,
  operations: {},
  selection: {type: 'code'},
});

const isCodeSource = (source: Source): source is CodeSource =>
  'object' === typeof source &&
  null !== source &&
  'code' === source.code &&
  'type' in source.selection &&
  'code' === source.selection.type;

export {getDefaultCodeSource, isCodeSource};
export type {CodeSource};
