import React, {useState} from 'react';
import styled from 'styled-components';
import {MeasurementFamily, filterOnLabelOrCode, getUnitIndex} from 'akeneomeasure/model/measurement-family';
import {MeasurementIllustration, Table} from 'akeneo-design-system';
import {UnitCode} from 'akeneomeasure/model/unit';
import {UnitDetails} from 'akeneomeasure/pages/edit/unit-tab/UnitDetails';
import {UnitRow} from 'akeneomeasure/pages/edit/unit-tab/UnitRow';
import {SearchBar, NoDataSection, NoDataTitle, ValidationError, filterErrors} from '@akeneo-pim-community/shared';
import {useTranslate, useUserContext} from '@akeneo-pim-community/legacy-bridge';
import {HeaderCell} from 'akeneomeasure/pages/common/Table';

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

  return (
    <TabContainer>
      <TabColumns>
        <UnitList>
          <SearchBar
            placeholder={translate('measurements.search.placeholder')}
            count={measurementFamily.units.length}
            searchValue={searchValue}
            onSearchChange={setSearchValue}
          />
          {0 === filteredUnits.length && (
            <NoDataSection>
              <MeasurementIllustration size={256} />
              <NoDataTitle>{translate('measurements.family.no_result.title')}</NoDataTitle>
            </NoDataSection>
          )}
          {0 < filteredUnits.length && (
            <Table>
              <Table.Header>
                <HeaderCell>{translate('pim_common.label')}</HeaderCell>
                <HeaderCell>{translate('pim_common.code')}</HeaderCell>
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
            </Table>
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
