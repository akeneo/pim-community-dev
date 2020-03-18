import React, {useState, useContext, FormEvent} from 'react';
import styled from 'styled-components';
import {
  MeasurementFamily,
  Unit,
  getUnitLabel,
  getUnit,
  filterOnLabelOrCode,
  setUnitSymbol,
  UnitCode,
  setUnitLabel,
  Operation,
  setUnitOperations,
} from 'akeneomeasure/model/measurement-family';
import {SearchBar} from 'akeneomeasure/shared/components/SearchBar';
import {TranslateContext} from 'akeneomeasure/context/translate-context';
import {UserContext} from 'akeneomeasure/context/user-context';
import {NoDataSection, NoDataTitle} from 'akeneomeasure/shared/components/NoData';
import {MeasurementFamilyIllustration} from 'akeneomeasure/shared/illustrations/MeasurementFamilyIllustration';
import {SubsectionHeader} from 'akeneomeasure/shared/components/Subsection';
import {FormGroup} from 'akeneomeasure/shared/components/FormGroup';
import {TextField} from 'akeneomeasure/shared/components/TextField';
import {useUiLocales} from 'akeneomeasure/shared/hooks/use-ui-locales';
import {OperationCollection} from 'akeneomeasure/pages/common/OperationCollection';
import {Button} from 'akeneomeasure/shared/components/Button';

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

const Footer = styled.div`
  background: ${props => props.theme.color.white};
  border-top: 1px solid ${props => props.theme.color.grey80};
  padding-top: 10px;
  position: sticky;
  bottom: 40px;
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
  onRowSelected: (unit: UnitCode) => void;
};

const UnitRow = ({unit, isStandardUnit, onRowSelected}: UnitRowProps) => {
  const __ = useContext(TranslateContext);
  const locale = useContext(UserContext)('uiLocale');

  return (
    <UnitContainer onClick={() => onRowSelected(unit.code)}>
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
  const [selectedUnitCode, selectUnitCode] = useState<UnitCode>(measurementFamily.standard_unit_code);
  const selectedUnit = getUnit(measurementFamily, selectedUnitCode);

  const filteredUnits = measurementFamily.units.filter(filterOnLabelOrCode(searchValue, locale));
  const locales = useUiLocales();

  if (undefined === selectedUnit) return null;

  return (
    <Container>
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
                  onRowSelected={selectUnitCode}
                />
              ))}
            </tbody>
          </TableContainer>
        )}
      </UnitList>
      <UnitDetails>
        <SubsectionHeader>
          {__('measurements.unit.title', {unitLabel: getUnitLabel(selectedUnit, locale)})}
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
            id="measurements.unit.properties.symbol"
            label={__('measurements.unit.symbol')}
            value={selectedUnit.symbol}
            onChange={(event: FormEvent<HTMLInputElement>) =>
              onMeasurementFamilyChange(setUnitSymbol(measurementFamily, selectedUnit.code, event.currentTarget.value))
            }
          />
          <OperationCollection
            onOperationsChange={(operations: Operation[]) => {
              onMeasurementFamilyChange(setUnitOperations(measurementFamily, selectedUnit.code, operations));
            }}
            operations={selectedUnit.convert_from_standard}
          />
        </FormGroup>
        <SubsectionHeader>{__('pim_common.label_translations')}</SubsectionHeader>
        <FormGroup>
          {null !== locales &&
            locales.map(locale => (
              <TextField
                id={`measurements.family.properties.label.${locale.code}`}
                label={locale.label}
                key={locale.code}
                value={measurementFamily.labels[locale.code] || ''}
                onChange={(event: FormEvent<HTMLInputElement>) =>
                  onMeasurementFamilyChange(
                    setUnitLabel(measurementFamily, selectedUnitCode, locale.code, event.currentTarget.value)
                  )
                }
              />
            ))}
        </FormGroup>
        <Footer>
          <Button>{__('pim_common.delete')}</Button>
        </Footer>
      </UnitDetails>
    </Container>
  );
};

export {UnitTab};
