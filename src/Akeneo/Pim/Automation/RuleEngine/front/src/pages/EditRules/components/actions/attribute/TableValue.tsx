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
  CellMappingContext,
  TableAttribute,
  TableValue,
} from '@akeneo-pim-ge/table_attribute';
import {
  getLabel,
  useTranslate,
  useUserContext,
} from '@akeneo-pim-community/shared';
import {ConfigContext} from '../../../../../context/ConfigContext';
import {InputValueProps} from './AttributeValue';
import {Helper} from 'akeneo-design-system';

const TableValueContainer = styled.div`
  width: calc((100vw - 580px) / 2);
`;

const HelperContainer = styled.div`
  width: calc((100vw - 580px) / 2);
  margin-top: 20px;
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
  const translate = useTranslate();
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
      <CellMappingContext.Provider value={{cellMatchersMapping, cellInputsMapping}}>
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
          />
        </TableValueContainer>
      </CellMappingContext.Provider>
      <HelperContainer>
        <Helper level='info' inline={true}>
          {translate(
            'pimee_catalog_rule.form.edit.actions.set_attribute.table_attribute_helper'
          )}
        </Helper>
      </HelperContainer>
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
