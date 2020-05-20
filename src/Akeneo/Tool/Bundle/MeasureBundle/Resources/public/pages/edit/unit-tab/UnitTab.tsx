import React, {useState} from 'react';
import styled from 'styled-components';
import {MeasurementFamily, filterOnLabelOrCode, getUnitIndex} from 'akeneomeasure/model/measurement-family';
import {NoDataSection, NoDataTitle} from 'akeneomeasure/shared/components/NoData';
import {MeasurementFamilyIllustration} from 'akeneomeasure/shared/illustrations/MeasurementFamilyIllustration';
import {Table, HeaderCell} from 'akeneomeasure/pages/common/Table';
import {ValidationError, filterErrors} from 'akeneomeasure/model/validation-error';
import {UnitCode} from 'akeneomeasure/model/unit';
import {UnitDetails} from 'akeneomeasure/pages/edit/unit-tab/UnitDetails';
import {UnitRow} from 'akeneomeasure/pages/edit/unit-tab/UnitRow';
import {SearchBar} from '@akeneo-pim-community/shared';
import {useTranslate, useUserContext} from '@akeneo-pim-community/legacy-bridge';

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
              <MeasurementFamilyIllustration size={256} />
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
