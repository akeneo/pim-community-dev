import {uuid} from 'akeneo-design-system';
import {Attribute} from '../../../models/Attribute';

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

export {getDefaultIdentifierSource};
export type {IdentifierSource};
