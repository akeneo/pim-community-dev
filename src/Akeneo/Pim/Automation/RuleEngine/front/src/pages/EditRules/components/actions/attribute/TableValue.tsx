import React, {useContext} from 'react';
import {
  TableAttribute,
  TableValue,
} from '@akeneo-pim-ge/table_attribute/src/models';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import styled from 'styled-components';
import {
  AddRowsButton,
  TableInputValue,
  TableValueWithId,
  useToggleRow,
  useUniqueIds,
} from '@akeneo-pim-ge/table_attribute/src/product';
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

  const [tableValue, setTableValue] = React.useState<TableValueWithId>(
    addUniqueIds(value || [])
  );
  const firstColumnCode = (attribute as TableAttribute).table_configuration[0]
    .code;

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
    <TableValueContainer>
      <AttributeLabel>
        {getLabel(
          attribute.labels,
          UserContext.get('catalogLocale'),
          attribute.code
        )}
        <AddRowsButton
          attribute={attribute as TableAttribute}
          columnCode={firstColumnCode}
          checkedOptionCodes={tableValue.map(
            row => (row[firstColumnCode] ?? '') as string
          )}
          toggleChange={handleToggleRow}
        />
      </AttributeLabel>
      <TableInputValue
        attribute={attribute as TableAttribute}
        valueData={tableValue}
        onChange={handleChange}
        cellInputsMapping={cellInputsMapping}
        cellMatchersMapping={cellMatchersMapping}
      />
    </TableValueContainer>
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
