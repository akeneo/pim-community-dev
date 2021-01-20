import React, {useState} from 'react';
import styled from 'styled-components';
import {MeasurementFamily, filterOnLabelOrCode, getUnitIndex} from '../../../model/measurement-family';
import {MeasurementIllustration} from 'akeneo-design-system';
import {Table, HeaderCell} from '../../../pages/common/Table';
import {UnitCode} from '../../../model/unit';
import {UnitDetails} from '../../../pages/edit/unit-tab/UnitDetails';
import {UnitRow} from '../../../pages/edit/unit-tab/UnitRow';
import {SearchBar, NoDataSection, NoDataTitle, ValidationError, filterErrors} from '@akeneo-pim-community/shared';
import {useTranslate, useUserContext} from '@akeneo-pim-community/legacy';

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
  const __ = useTranslate();
  const locale = useUserContext().get('uiLocale');
  const [searchValue, setSearchValue] = useState('');

  const filteredUnits = measurementFamily.units.filter(filterOnLabelOrCode(searchValue, locale));

  return (
    <TabContainer>
      <TabColumns>
        <UnitList>
          <SearchBar
            placeholder={__('measurements.search.placeholder')}
            count={measurementFamily.units.length}
            searchValue={searchValue}
            onSearchChange={setSearchValue}
          />
          {0 === filteredUnits.length && (
            <NoDataSection>
              <MeasurementIllustration size={256} />
              <NoDataTitle>{__('measurements.family.no_result.title')}</NoDataTitle>
            </NoDataSection>
          )}
          {0 < filteredUnits.length && (
            <Table>
              <thead>
                <tr>
                  <HeaderCell title={__('pim_common.label')}>{__('pim_common.label')}</HeaderCell>
                  <HeaderCell title={__('pim_common.code')}>{__('pim_common.code')}</HeaderCell>
                </tr>
              </thead>
              <tbody>
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
              </tbody>
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
