import React, {useContext} from 'react';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import styled from 'styled-components';
import {
  AddRowsButton,
  TableInputValue,
  TableValueWithId,
  useToggleRow,
  useUniqueIds,
  AttributeContext,
  TableAttribute,
  TableValue,
} from '@akeneo-pim-ge/table_attribute';
import {getLabel, useUserContext} from '@akeneo-pim-community/shared';
import {ConfigContext} from '../../../../../context/ConfigContext';
import {InputValueProps} from './AttributeValue';

const TableValueContainer = styled.div`
  width: calc((100vw - 580px) / 2);
`;

const AttributeLabel = styled.div`
  padding-bottom: 5px;
  display: flex;
  justify-content: space-between;
`;

const TableValue: React.FC<InputValueProps> = ({
  attribute,
  value,
  onChange,
}) => {
  const UserContext = useUserContext();
  const {cellMatchersMapping, cellInputsMapping} = useContext(ConfigContext);
  const {addUniqueIds, removeUniqueIds} = useUniqueIds();
  const [attributeState, setAttributeState] = React.useState<TableAttribute>(
    attribute as TableAttribute
  );

  const [tableValue, setTableValue] = React.useState<TableValueWithId>(
    addUniqueIds(value || [])
  );
  const firstColumnCode = attributeState.table_configuration[0].code;

  const handleChange = (value: TableValueWithId) => {
    setTableValue(value);
    onChange(removeUniqueIds(value));
  };

  const handleToggleRow = useToggleRow(
    tableValue,
    firstColumnCode,
    handleChange
  );

  return (
    <AttributeContext.Provider
      value={{attribute: attributeState, setAttribute: setAttributeState}}>
      <TableValueContainer>
        <AttributeLabel>
          {getLabel(
            attributeState.labels,
            UserContext.get('catalogLocale'),
            attributeState.code
          )}
          <AddRowsButton
            checkedOptionCodes={tableValue.map(
              row => (row[firstColumnCode] ?? '') as string
            )}
            toggleChange={handleToggleRow}
          />
        </AttributeLabel>
        <TableInputValue
          valueData={tableValue}
          onChange={handleChange}
          cellInputsMapping={cellInputsMapping}
          cellMatchersMapping={cellMatchersMapping}
        />
      </TableValueContainer>
    </AttributeContext.Provider>
  );
};

const render: (props: InputValueProps) => JSX.Element = props => {
  return (
    <DependenciesProvider>
      <TableValue {...props} key={props.attribute.code} />
    </DependenciesProvider>
  );
};

export default render;
