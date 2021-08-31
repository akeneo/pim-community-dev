import React from 'react';
import {Attribute, ScopeCode} from 'rule_definition/src/models';
import {TableAttribute} from '../models/Attribute';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import styled from 'styled-components';
import {TableInputValue} from '../product/TableInputValue';
import {AddRowsButton} from '../product/AddRowsButton';
import {TableValue} from '../models/TableValue';
import {TableValueWithId} from '../product/TableFieldApp';
import {getLabel, useUserContext} from '@akeneo-pim-community/shared';
import {useUniqueIds} from '../product/useUniqueIds';
import {useToggleRow} from '../product/useToggleRow';

const TableValueContainer = styled.div`
  width: calc((100vw - 580px) / 2);
`;

const AttributeLabel = styled.div`
  padding-bottom: 5px;
  display: flex;
  justify-content: space-between;
`;

export type InputValueProps = {
  id: string;
  attribute: Attribute;
  name: string;
  validation?: {required?: string; validate?: (value: any) => string | true};
  value: any;
  label?: string;
  onChange: (value: any) => void;
  scopeCode?: ScopeCode;
};

const TableValue: React.FC<InputValueProps> = ({attribute, value, onChange}) => {
  const UserContext = useUserContext();
  const {addUniqueIds, removeUniqueIds} = useUniqueIds();

  const [tableValue, setTableValue] = React.useState<TableValueWithId>(addUniqueIds(value || []));
  const firstColumnCode = (attribute as TableAttribute).table_configuration[0].code;

  const handleChange = (value: TableValueWithId) => {
    setTableValue(value);
    onChange(removeUniqueIds(value));
  };

  const handleToggleRow = useToggleRow(tableValue, firstColumnCode, handleChange);

  return (
    <TableValueContainer>
      <AttributeLabel>
        {getLabel(attribute.labels, UserContext.get('catalogLocale'), attribute.code)}
        <AddRowsButton
          attribute={attribute as TableAttribute}
          columnCode={firstColumnCode}
          checkedOptionCodes={tableValue.map(row => (row[firstColumnCode] ?? '') as string)}
          toggleChange={handleToggleRow}
        />
      </AttributeLabel>
      <TableInputValue attribute={attribute as TableAttribute} valueData={tableValue} onChange={handleChange} />
    </TableValueContainer>
  );
};

const render: (props: InputValueProps) => JSX.Element = props => {
  return (
    <DependenciesProvider>
      <TableValue {...props} />
    </DependenciesProvider>
  );
};

export default render;
