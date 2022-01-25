import {LabelCollection} from '@akeneo-pim-community/shared';
import {ReferenceEntityIdentifierOrCode} from './ReferenceEntity';

export type DataType = 'text' | 'number' | 'boolean' | 'select' | 'reference_entity';
export type ColumnCode = string;

export type TextColumnValidation = {
  max_length?: number;
};

export type NumberColumnValidation = {
  min?: number;
  max?: number;
  decimals_allowed?: boolean;
};

type BooleanColumnValidation = {};

type SelectColumnValidation = {};

type ReferenceEntityColumnValidation = {};

export type ColumnValidation =
  | TextColumnValidation
  | NumberColumnValidation
  | BooleanColumnValidation
  | ReferenceEntityColumnValidation;

export type SelectOptionCode = string;

export type SelectOption = {
  code: SelectOptionCode;
  labels: LabelCollection;
};

type RecordOptionCode = string;

export type RecordOption = {
  code: RecordOptionCode;
};

export type TextColumnDefinition = {
  code: ColumnCode;
  labels: LabelCollection;
  data_type: 'text';
  validations: TextColumnValidation;
  is_required_for_completeness?: boolean;
};

export type NumberColumnDefinition = {
  code: ColumnCode;
  labels: LabelCollection;
  data_type: 'number';
  validations: NumberColumnValidation;
  is_required_for_completeness?: boolean;
};

export type BooleanColumnDefinition = {
  code: ColumnCode;
  labels: LabelCollection;
  data_type: 'boolean';
  validations: BooleanColumnValidation;
  is_required_for_completeness?: boolean;
};

export type SelectColumnDefinition = {
  code: ColumnCode;
  labels: LabelCollection;
  data_type: 'select';
  validations: SelectColumnValidation;
  is_required_for_completeness?: boolean;
  options?: SelectOption[];
};

export type ReferenceEntityColumnDefinition = {
  code: ColumnCode;
  labels: LabelCollection;
  data_type: 'reference_entity';
  validations: ReferenceEntityColumnValidation;
  is_required_for_completeness?: boolean;
  reference_entity_identifier: ReferenceEntityIdentifierOrCode;
};

export type ColumnDefinition =
  | TextColumnDefinition
  | NumberColumnDefinition
  | BooleanColumnDefinition
  | SelectColumnDefinition
  | ReferenceEntityColumnDefinition;

export type TableConfiguration = ColumnDefinition[];

export const isColumnCodeNotAvailable: (columnCode: ColumnCode) => boolean = columnCode =>
  ['product', 'product_model', 'attribute'].includes(columnCode.toLowerCase());

const castSelectColumnDefinition: (columnDefinition: ColumnDefinition) => SelectColumnDefinition = columnDefinition => {
  if (columnDefinition.data_type !== 'select') {
    throw new Error(`Column definition should have 'select' data_type, '${columnDefinition.data_type}' given)`);
  }
  return columnDefinition;
};

const castReferenceEntityColumnDefinition: (columnDefinition: ColumnDefinition) => ReferenceEntityColumnDefinition =
  columnDefinition => {
    if (columnDefinition.data_type !== 'reference_entity') {
      throw new Error(
        `Column definition should have 'reference_entity' data_type, '${columnDefinition.data_type}' given)`
      );
    }
    return columnDefinition;
  };

export {castSelectColumnDefinition, castReferenceEntityColumnDefinition};
