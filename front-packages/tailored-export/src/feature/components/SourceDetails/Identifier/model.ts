import {uuid} from 'akeneo-design-system';
import {Source, Attribute} from '../../../models';

type IdentifierSource = {
  uuid: string;
  code: string;
  type: 'attribute';
  locale: null;
  channel: null;
  operations: {};
  selection: {type: 'code'};
};

const getDefaultIdentifierSource = (attribute: Attribute): IdentifierSource => ({
  uuid: uuid(),
  code: attribute.code,
  type: 'attribute',
  locale: null,
  channel: null,
  operations: {},
  selection: {type: 'code'},
});

const isIdentifierSource = (source: Source): source is IdentifierSource =>
  'type' in source.selection && 'code' === source.selection.type;

export {getDefaultIdentifierSource, isIdentifierSource};
export type {IdentifierSource};
