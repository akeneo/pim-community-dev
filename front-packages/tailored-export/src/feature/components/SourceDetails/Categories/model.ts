import {uuid} from 'akeneo-design-system';
import {Source} from '../../../models';
import {CodeLabelCollectionSelection} from '../common';

type CategoriesSource = {
  uuid: string;
  code: 'categories';
  type: 'property';
  locale: null;
  channel: null;
  operations: {};
  selection: CodeLabelCollectionSelection;
};

const getDefaultCategoriesSource = (): CategoriesSource => ({
  uuid: uuid(),
  code: 'categories',
  type: 'property',
  locale: null,
  channel: null,
  operations: {},
  selection: {type: 'code', separator: ','},
});

const isCategoriesSource = (source: Source): source is CategoriesSource => 'categories' === source.code;

export {getDefaultCategoriesSource, isCategoriesSource};
export type {CategoriesSource};
