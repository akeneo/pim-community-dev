import {Attribute, ColumnDefinition} from '../../models';
import {ReactElement} from 'react';

type ColumnDefinitionProps = {
  attribute: Attribute;
  selectedColumn: ColumnDefinition;
  handleChange: (newColumn: ColumnDefinition) => void;
};

export type ColumnDefinitionPropertiesMapping = {
  [attributeType: string]: {default: ColumnProperties};
};

export type ColumnProperties = (props: ColumnDefinitionProps) => ReactElement;
