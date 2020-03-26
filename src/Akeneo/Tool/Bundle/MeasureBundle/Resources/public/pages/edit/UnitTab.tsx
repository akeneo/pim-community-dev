import React, {useState, useContext} from 'react';
import styled from 'styled-components';
import {MeasurementFamily, filterOnLabelOrCode} from 'akeneomeasure/model/measurement-family';
import {SearchBar} from 'akeneomeasure/shared/components/SearchBar';
import {TranslateContext} from 'akeneomeasure/context/translate-context';
import {UserContext} from 'akeneomeasure/context/user-context';
import {NoDataSection, NoDataTitle} from 'akeneomeasure/shared/components/NoData';
import {MeasurementFamilyIllustration} from 'akeneomeasure/shared/illustrations/MeasurementFamilyIllustration';
import {Table, HeaderCell, Row, LabelCell} from 'akeneomeasure/pages/common/Table';
import {ValidationError, filterErrors} from 'akeneomeasure/model/validation-error';
import {Unit, UnitCode, getUnitLabel} from 'akeneomeasure/model/unit';
import {ErrorBadge} from 'akeneomeasure/shared/components/ErrorBadge';
import {UnitDetails} from 'akeneomeasure/pages/edit/unit-tab/UnitDetails';

const UnitList = styled.div`
  flex: 1;
  overflow: auto;
`;

const CodeCell = styled.td`
  padding-right: 15px;

  > span {
    display: flex;
    align-items: center;

    span:first-child {
      flex: 1;
    }
  }
`;

const StandardUnitBadge = styled.span`
  background-color: white;
  border: 1px solid ${props => props.theme.color.grey100};
  font-size: ${props => props.theme.fontSize.small};
  color: ${props => props.theme.color.grey120};
  border-radius: 2px;
  padding: 0 5px;
  text-transform: uppercase;

  :not(:last-child) {
    margin-right: 15px;
  }
`;

type UnitRowProps = {
  unit: Unit;
  isStandardUnit: boolean;
  isSelected?: boolean;
  invalid?: boolean;
  onRowSelected: (unitCode: UnitCode) => void;
};

const UnitRow = ({unit, isStandardUnit, isSelected = false, invalid = false, onRowSelected}: UnitRowProps) => {
  const __ = useContext(TranslateContext);
  const locale = useContext(UserContext)('uiLocale');

  return (
    <Row isSelected={isSelected} onClick={() => onRowSelected(unit.code)}>
      <LabelCell>{getUnitLabel(unit, locale)}</LabelCell>
      <CodeCell>
        <span>
          <span>{unit.code}</span>
          {isStandardUnit && <StandardUnitBadge>{__('measurements.family.standard_unit')}</StandardUnitBadge>}
          {invalid && <ErrorBadge />}
        </span>
      </CodeCell>
    </Row>
  );
};

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
  const __ = useContext(TranslateContext);
  const locale = useContext(UserContext)('uiLocale');
  const [searchValue, setSearchValue] = useState('');

  const filteredUnits = measurementFamily.units.filter(filterOnLabelOrCode(searchValue, locale));

  return (
    <>
      <UnitList>
        <SearchBar count={measurementFamily.units.length} searchValue={searchValue} onSearchChange={setSearchValue} />
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
                  invalid={0 < filterErrors(errors, `[${index}]`).length}
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
        errors={errors}
      />
    </>
  );
};

export {UnitTab};
