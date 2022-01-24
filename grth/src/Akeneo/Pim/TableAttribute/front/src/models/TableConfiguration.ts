import {LabelCollection} from '@akeneo-pim-community/shared';
import {ReferenceEntityIdentifierOrCode} from './ReferenceEntity';
import {MeasurementFamilyCode, MeasurementUnitCode} from "./MeasurementFamily";

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

type MeasurementColumnValidation = {};

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

type ColumnDefinitionCommon<T, DT> = {
  code: ColumnCode;
  labels: LabelCollection;
  data_type: DT;
  validations: T;
  is_required_for_completeness?: boolean
}

export type TextColumnDefinition = ColumnDefinitionCommon<NumberColumnValidation, 'text'>

export type NumberColumnDefinition = ColumnDefinitionCommon<NumberColumnValidation, 'number'>

export type BooleanColumnDefinition = ColumnDefinitionCommon<BooleanColumnValidation, 'boolean'>

export type SelectColumnDefinition = ColumnDefinitionCommon<SelectColumnValidation, 'select'> & {
  options?: SelectOption[];
}

export type ReferenceEntityColumnDefinition = ColumnDefinitionCommon<ReferenceEntityColumnValidation, 'reference_entity'> & {
  reference_entity_identifier: ReferenceEntityIdentifierOrCode;
}

export type MeasurementColumnDefinition = ColumnDefinitionCommon<MeasurementColumnValidation, 'measurement'> & {
  measurementFamilyCode: MeasurementFamilyCode;
  measurementDefaultUnitCode: MeasurementUnitCode;
}

export type ColumnDefinition =
  | TextColumnDefinition
  | NumberColumnDefinition
  | BooleanColumnDefinition
  | SelectColumnDefinition
  | ReferenceEntityColumnDefinition
  | MeasurementColumnDefinition;

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
