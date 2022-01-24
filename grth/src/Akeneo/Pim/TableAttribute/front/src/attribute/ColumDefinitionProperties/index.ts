import {ColumnDefinition, TableAttribute} from '../../models';
import {ReactElement} from 'react';
import SelectProperties from './SelectProperties';
import NumberProperties from './NumberProperties';
import TextProperties from './TextProperties';
import ReferenceEntityProperties from './ReferenceEntityProperties';

type ColumnDefinitionProps = {
  attribute: TableAttribute;
  selectedColumn: ColumnDefinition;
  handleChange: (newColumn: ColumnDefinition) => void;
};

export type ColumnProperties = (props: ColumnDefinitionProps) => ReactElement;

const ColumnDefinitions = {
  select: SelectProperties,
  number: NumberProperties,
  text: TextProperties,
  reference_entity: ReferenceEntityProperties,
};

export {ColumnDefinitions};
