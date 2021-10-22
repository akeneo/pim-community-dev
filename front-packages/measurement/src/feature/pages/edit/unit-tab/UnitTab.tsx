import React, {useRef, useState} from 'react';
import styled from 'styled-components';
import {MeasurementFamily, filterOnLabelOrCode, getUnitIndex} from '../../../model/measurement-family';
import {MeasurementIllustration, Search, Table, useAutoFocus} from 'akeneo-design-system';
import {UnitCode} from '../../../model/unit';
import {UnitDetails} from './UnitDetails';
import {UnitRow} from './UnitRow';
import {
  NoDataSection,
  NoDataTitle,
  ValidationError,
  filterErrors,
  useTranslate,
  useUserContext,
} from '@akeneo-pim-community/shared';

const SpacedTable = styled(Table)`
  th {
    padding-top: 15px;
  }
`;

const TabContainer = styled.div`
  display: flex;
  flex-direction: column;
  height: calc(100%);
  width: 100%;
`;

const TabColumns = styled.div`
  display: flex;
  overflow: hidden;
`;

const UnitList = styled.div`
  flex: 1;
  overflow: auto;
`;

type UnitTabProps = {
  measurementFamily: MeasurementFamily;
  errors: ValidationError[];
  onMeasurementFamilyChange: (measurementFamily: MeasurementFamily) => void;
  selectedUnitCode: UnitCode;
  selectUnitCode: (unitCode: UnitCode) => void;
};

const UnitTab = ({
  measurementFamily,
  errors,
  onMeasurementFamilyChange,
  selectedUnitCode,
  selectUnitCode,
}: UnitTabProps) => {
  const translate = useTranslate();
  const locale = useUserContext().get('uiLocale');
  const [searchValue, setSearchValue] = useState('');

  const filteredUnits = measurementFamily.units.filter(filterOnLabelOrCode(searchValue, locale));
  const inputRef = useRef<HTMLInputElement>(null);

  useAutoFocus(inputRef);

  return (
    <TabContainer>
      <TabColumns>
        <UnitList>
          <Search
            sticky={0}
            placeholder={translate('measurements.search.placeholder')}
            searchValue={searchValue}
            onSearchChange={setSearchValue}
            inputRef={inputRef}
          >
            <Search.ResultCount>
              {translate('pim_common.result_count', {itemsCount: filteredUnits.length}, filteredUnits.length)}
            </Search.ResultCount>
          </Search>
          {0 === filteredUnits.length && (
            <NoDataSection>
              <MeasurementIllustration size={256} />
              <NoDataTitle>{translate('pim_common.no_search_result')}</NoDataTitle>
            </NoDataSection>
          )}
          {0 < filteredUnits.length && (
            <SpacedTable>
              <Table.Header sticky={44}>
                <Table.HeaderCell>{translate('pim_common.label')}</Table.HeaderCell>
                <Table.HeaderCell>{translate('pim_common.code')}</Table.HeaderCell>
              </Table.Header>
              <Table.Body>
                {filteredUnits.map((unit, index) => (
                  <UnitRow
                    key={unit.code}
                    unit={unit}
                    isStandardUnit={unit.code === measurementFamily.standard_unit_code}
                    isSelected={unit.code === selectedUnitCode}
                    isInvalid={0 < filterErrors(errors, `[${index}]`).length}
                    onRowSelected={selectUnitCode}
                  />
                ))}
              </Table.Body>
            </SpacedTable>
          )}
        </UnitList>
        <UnitDetails
          measurementFamily={measurementFamily}
          selectedUnitCode={selectedUnitCode}
          onMeasurementFamilyChange={onMeasurementFamilyChange}
          selectUnitCode={selectUnitCode}
          errors={filterErrors(errors, `[${getUnitIndex(measurementFamily, selectedUnitCode)}]`)}
        />
      </TabColumns>
    </TabContainer>
  );
};

export {UnitTab};
