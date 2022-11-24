import {LabelCollection} from '@akeneo-pim-community/shared';
import {Attribute} from './Attribute';

export type Template = {
  uuid: string;
  code: string;
  labels: LabelCollection;
  category_tree_identifier: number;
  attributes: Attribute[];
};
