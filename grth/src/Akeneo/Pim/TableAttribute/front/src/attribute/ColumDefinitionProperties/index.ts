import {ColumnDefinition, TableAttribute} from '../../models';
import {ReactElement} from 'react';
import SelectProperties from './SelectProperties';
import NumberProperties from './NumberProperties';
import TextProperties from './TextProperties';
import ReferenceEntityProperties from './ReferenceEntityProperties';
import MeasurementProperties from './MeasurementProperties';

type ColumnDefinitionProps = {
  attribute: TableAttribute;
  selectedColumn: ColumnDefinition;
  handleChange: (newColumn: ColumnDefinition) => void;
};

export type ColumnProperties = (props: ColumnDefinitionProps) => ReactElement;

const ColumnDefinitions: {[dataType: string]: ColumnProperties} = {
  select: SelectProperties,
  number: NumberProperties,
  text: TextProperties,
  reference_entity: ReferenceEntityProperties,
  measurement: MeasurementProperties,
};

export {ColumnDefinitions};
