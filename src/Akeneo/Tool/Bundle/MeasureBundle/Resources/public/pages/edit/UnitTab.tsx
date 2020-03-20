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
  removeUnit,
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
import {Table, HeaderCell, Row, LabelCell} from 'akeneomeasure/pages/common/Table';

const UnitList = styled.div`
  flex: 1;
  overflow: auto;
`;

const UnitDetails = styled.div`
  margin-left: 40px;
  width: 400px;
  overflow: auto;
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
  padding: 10px 0 40px;
  position: sticky;
  bottom: 0;
  display: flex;
  justify-content: flex-end;
  z-index: 10;
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
  isSelected?: boolean;
  onRowSelected: (unitCode: UnitCode) => void;
};

const UnitRow = ({unit, isStandardUnit, isSelected = false, onRowSelected}: UnitRowProps) => {
  const __ = useContext(TranslateContext);
  const locale = useContext(UserContext)('uiLocale');

  return (
    <Row isSelected={isSelected} onClick={() => onRowSelected(unit.code)}>
      <LabelCell>{getUnitLabel(unit, locale)}</LabelCell>
      <CodeCell>
        <span>
          <span>{unit.code}</span>
          {isStandardUnit && <StandardUnitBadge>{__('measurements.family.standard_unit')}</StandardUnitBadge>}
        </span>
      </CodeCell>
    </Row>
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
              {filteredUnits.map(unit => (
                <UnitRow
                  key={unit.code}
                  unit={unit}
                  isStandardUnit={unit.code === measurementFamily.standard_unit_code}
                  isSelected={unit.code === selectedUnitCode}
                  onRowSelected={selectUnitCode}
                />
              ))}
            </tbody>
          </Table>
        )}
      </UnitList>
      <UnitDetails>
        <SubsectionHeader top={0}>
          {__('measurements.unit.title', {unitLabel: getUnitLabel(selectedUnit, locale)})}
        </SubsectionHeader>
        <FormGroup>
          <TextField
            id="measurements.unit.properties.code"
            label={__('pim_common.code')}
            value={selectedUnit.code}
            required={true}
            readOnly={true}
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
            operations={selectedUnit.convert_from_standard}
            onOperationsChange={(operations: Operation[]) => {
              onMeasurementFamilyChange(setUnitOperations(measurementFamily, selectedUnit.code, operations));
            }}
          />
        </FormGroup>
        <FormGroup>
          <SubsectionHeader top={0}>{__('measurements.label_translations')}</SubsectionHeader>
          <FormGroup>
            {null !== locales &&
              locales.map(locale => (
                <TextField
                  id={`measurements.family.properties.label.${locale.code}`}
                  label={locale.label}
                  key={locale.code}
                  flag={locale.code}
                  value={selectedUnit.labels[locale.code] || ''}
                  onChange={(event: FormEvent<HTMLInputElement>) =>
                    onMeasurementFamilyChange(
                      setUnitLabel(measurementFamily, selectedUnitCode, locale.code, event.currentTarget.value)
                    )
                  }
                />
              ))}
          </FormGroup>
        </FormGroup>
        {selectedUnitCode !== measurementFamily.standard_unit_code && (
          <Footer>
            <Button
              color="red"
              outline={true}
              onClick={() => {
                onMeasurementFamilyChange(removeUnit(measurementFamily, selectedUnitCode));
                selectUnitCode(measurementFamily.standard_unit_code);
              }}
            >
              {__('measurements.unit.delete')}
            </Button>
          </Footer>
        )}
      </UnitDetails>
    </>
  );
};

export {UnitTab};
