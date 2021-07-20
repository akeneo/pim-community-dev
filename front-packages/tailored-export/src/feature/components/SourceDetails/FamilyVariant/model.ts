import {uuid} from 'akeneo-design-system';
import {Source} from '../../../models';
import {CodeLabelSelection} from '../common/CodeLabelSelector';

type FamilyVariantSource = {
  uuid: string;
  code: 'family_variant';
  type: 'property';
  locale: null;
  channel: null;
  operations: {};
  selection: CodeLabelSelection;
};

const getDefaultFamilyVariantSource = (): FamilyVariantSource => ({
  uuid: uuid(),
  code: 'family_variant',
  type: 'property',
  locale: null,
  channel: null,
  operations: {},
  selection: {type: 'code'},
});

const isFamilyVariantSource = (source: Source): source is FamilyVariantSource => 'family_variant' === source.code;

export {isFamilyVariantSource, getDefaultFamilyVariantSource};
export type {FamilyVariantSource};
