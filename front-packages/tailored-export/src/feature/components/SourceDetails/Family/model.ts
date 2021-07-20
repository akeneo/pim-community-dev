import {uuid} from 'akeneo-design-system';
import {Source} from '../../../models';
import {CodeLabelSelection} from '../common/CodeLabelSelector';

type FamilySource = {
  uuid: string;
  code: 'family';
  type: 'property';
  locale: null;
  channel: null;
  operations: {};
  selection: CodeLabelSelection;
};

const getDefaultFamilySource = (): FamilySource => ({
  uuid: uuid(),
  code: 'family',
  type: 'property',
  locale: null,
  channel: null,
  operations: {},
  selection: {type: 'code'},
});

const isFamilySource = (source: Source): source is FamilySource => 'family' === source.code;

export {isFamilySource, getDefaultFamilySource};
export type {FamilySource};
