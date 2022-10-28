import {LabelCollection} from '@akeneo-pim-community/shared';
import {Attribute} from './Attribute';
import {CategoryImageAttributeValueData, CategoryImageAttributeValueDataFileInfo} from "./Category";

export type Template = {
  uuid: string;
  code: string;
  labels: LabelCollection;
  category_tree_identifier: number;
  attributes: Attribute[];
};

export interface TemplateImageAttributeValueDataFileInfo {
  size?: number;
  file_path: string;
  mime_type?: string;
  extension?: string;
  original_filename: string;
}

type TemplateTextAttributeValueData = string;

export type TemplateImageAttributeValueData = TemplateImageAttributeValueDataFileInfo | null;

export type TemplateAttributeValueData = TemplateTextAttributeValueData | TemplateImageAttributeValueData;
