import React, {useState, useContext, FormEvent} from 'react';
import styled from 'styled-components';
import {
  MeasurementFamily,
  Unit,
  getUnitLabel,
  filterOnLabelOrCode,
  getStandardUnit,
  setUnitLabel,
} from 'akeneomeasure/model/measurement-family';
import {SearchBar} from 'akeneomeasure/shared/components/SearchBar';
import {TranslateContext} from 'akeneomeasure/context/translate-context';
import {UserContext} from 'akeneomeasure/context/user-context';
import {NoDataSection, NoDataTitle} from 'akeneomeasure/shared/components/NoData';
import {MeasurementFamilyIllustration} from 'akeneomeasure/shared/illustrations/MeasurementFamilyIllustration';
import {SubsectionHeader} from 'akeneomeasure/shared/components/Subsection';
import {FormGroup} from 'akeneomeasure/shared/components/FormGroup';
import {TextField} from 'akeneomeasure/shared/components/TextField';

const Container = styled.div`
  display: flex;
`;

const UnitList = styled.div`
  flex: 1;
`;

const UnitDetails = styled.div`
  width: 400px;
  margin-left: 40px;
`;

const StickySearchBar = styled(SearchBar)`
  position: sticky;
`;

const TableContainer = styled.table`
  width: 100%;
  color: ${props => props.theme.color.grey140};
  border-collapse: collapse;

  td {
    width: 25%;
  }
`;

const HeaderCell = styled.th`
  text-align: left;
  font-weight: normal;
  position: sticky;
  height: 43px;
  box-shadow: 0 1px 0 ${props => props.theme.color.grey120};
  background: ${props => props.theme.color.white};
`;

// TODO factorize?
const UnitContainer = styled.tr`
  cursor: pointer;
  height: 54px;
  border-bottom: 1px solid ${props => props.theme.color.grey70};
`;

//TODO factorize with MeasurementFamilyRow/LabelCell?
const LabelCell = styled.td`
  color: ${props => props.theme.color.purple100};
  font-style: italic;
  font-weight: bold;
`;

const CodeCell = styled.td`
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
  margin-right: 10px;
`;

type UnitRowProps = {
  unit: Unit;
  isStandardUnit: boolean;
  onRowSelected: (unit: Unit) => void;
};

const UnitRow = ({unit, isStandardUnit, onRowSelected}: UnitRowProps) => {
  const __ = useContext(TranslateContext);
  const locale = useContext(UserContext)('uiLocale');

  return (
    <UnitContainer onClick={() => onRowSelected(unit)}>
      <LabelCell>{getUnitLabel(unit, locale)}</LabelCell>
      <CodeCell>
        <span>
          <span>{unit.code}</span>
          {isStandardUnit && <StandardUnitBadge>{__('measurements.family.standard_unit')}</StandardUnitBadge>}
        </span>
      </CodeCell>
    </UnitContainer>
  );
};

const UnitTab = ({
  measurementFamily,
  onMeasurementFamilyChange,
}: {
  measurementFamily: MeasurementFamily;
  onMeasurementFamilyChange: (measurementFamily: MeasurementFamily) => void;
}) => {
  const __ = useContext(TranslateContext);
  const locale = useContext(UserContext)('uiLocale');
  const [searchValue, setSearchValue] = useState('');
  const [selectedUnit, selectUnit] = useState<Unit>(getStandardUnit(measurementFamily));

  const filteredUnits = measurementFamily.units.filter(filterOnLabelOrCode(searchValue, locale));

  return (
    <Container
      onClick={() => {
        onMeasurementFamilyChange({...measurementFamily, code: 'nice from unit tab'});
      }}
    >
      <UnitList>
        <StickySearchBar
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
          <TableContainer>
            <thead>
              <tr>
                <HeaderCell title={__('pim_common.label')}>{__('pim_common.label')}</HeaderCell>
                <HeaderCell title={__('pim_common.code')}>{__('pim_common.code')}</HeaderCell>
              </tr>
            </thead>
            <tbody>
              {filteredUnits.map(unit => (
                <UnitRow
                  key={unit.code}
                  unit={unit}
                  isStandardUnit={unit.code === measurementFamily.standard_unit_code}
                  onRowSelected={selectUnit}
                />
              ))}
            </tbody>
          </TableContainer>
        )}
      </UnitList>
      <UnitDetails>
        <SubsectionHeader>
          {__('measurements.unit.edit.title', {unitLabel: getUnitLabel(selectedUnit, locale)})}
        </SubsectionHeader>
        <FormGroup>
          <TextField
            id="measurements.unit.properties.code"
            label={__('pim_common.code')}
            value={selectedUnit.code}
            required
            readOnly
          />
          <TextField
            id="measurements.unit.properties.code"
            label={__('pim_common.code')}
            value={selectedUnit.code}
            onChange={(event: FormEvent<HTMLInputElement>) =>
              onMeasurementFamilyChange(
                setUnitLabel(measurementFamily, selectedUnit.code, locale, event.currentTarget.value)
              )
            }
          />
        </FormGroup>
      </UnitDetails>
    </Container>
  );
};

export {UnitTab};
