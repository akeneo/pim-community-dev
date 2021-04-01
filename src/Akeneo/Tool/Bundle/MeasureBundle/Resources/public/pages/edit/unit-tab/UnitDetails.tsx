import React, {useCallback} from 'react';
import styled from 'styled-components';
import {OperationCollection} from 'akeneomeasure/pages/common/OperationCollection';
import {
  setUnitSymbol,
  setUnitLabel,
  setUnitOperations,
  MeasurementFamily,
  getUnit,
  removeUnit,
} from 'akeneomeasure/model/measurement-family';
import {Operation} from 'akeneomeasure/model/operation';
import {useUiLocales} from 'akeneomeasure/shared/hooks/use-ui-locales';
import {UnitCode, getUnitLabel} from 'akeneomeasure/model/unit';
import {useTranslate, useUserContext, useSecurity} from '@akeneo-pim-community/legacy-bridge';
import {
  filterErrors,
  ValidationError,
  useToggleState,
  TextField,
  Section,
  DeleteModal,
} from '@akeneo-pim-community/shared';
import {Button, getColor, SectionTitle} from 'akeneo-design-system';

const Container = styled.div`
  margin-left: 40px;
  width: 400px;
  overflow: auto;
`;

const Footer = styled.div`
  background: ${getColor('white')};
  border-top: 1px solid ${getColor('grey', 80)};
  padding: 10px 0 40px;
  position: sticky;
  bottom: 0;
  display: flex;
  justify-content: flex-end;
  z-index: 10;
`;

type UnitDetailsProps = {
  measurementFamily: MeasurementFamily;
  selectedUnitCode: UnitCode;
  onMeasurementFamilyChange: (measurementFamily: MeasurementFamily) => void;
  selectUnitCode: (unitCode: UnitCode) => void;
  errors: ValidationError[];
};

const UnitDetails = ({
  measurementFamily,
  selectedUnitCode,
  onMeasurementFamilyChange,
  selectUnitCode,
  errors,
}: UnitDetailsProps) => {
  const translate = useTranslate();
  const {isGranted} = useSecurity();
  const locales = useUiLocales();
  const locale = useUserContext().get('uiLocale');
  const selectedUnit = getUnit(measurementFamily, selectedUnitCode);
  const [isConfirmDeleteUnitModalOpen, openConfirmDeleteUnitModal, closeConfirmDeleteUnitModal] = useToggleState(false);

  const handleRemoveUnit = useCallback(() => {
    onMeasurementFamilyChange(removeUnit(measurementFamily, selectedUnitCode));
    selectUnitCode(measurementFamily.standard_unit_code);
    closeConfirmDeleteUnitModal();
  }, [measurementFamily, selectedUnitCode, onMeasurementFamilyChange, selectUnitCode, removeUnit]);

  if (undefined === selectedUnit) return null;

  return (
    <>
      {isConfirmDeleteUnitModalOpen && (
        <DeleteModal
          title={translate('measurements.title.measurement')}
          onConfirm={handleRemoveUnit}
          onCancel={closeConfirmDeleteUnitModal}
        >
          {translate('measurements.unit.delete.confirm')}
        </DeleteModal>
      )}
      <Container>
        <Section>
          <SectionTitle sticky={0}>
            <SectionTitle.Title>
              {translate('measurements.unit.title', {unitLabel: getUnitLabel(selectedUnit, locale)})}
            </SectionTitle.Title>
          </SectionTitle>
          <TextField
            label={translate('pim_common.code')}
            value={selectedUnit.code}
            required={true}
            readOnly={true}
            errors={filterErrors(errors, '[code]')}
          />
          <TextField
            label={translate('measurements.unit.symbol')}
            value={selectedUnit.symbol}
            readOnly={!isGranted('akeneo_measurements_measurement_unit_edit')}
            onChange={value => onMeasurementFamilyChange(setUnitSymbol(measurementFamily, selectedUnit.code, value))}
            errors={filterErrors(errors, '[symbol]')}
          />
          <OperationCollection
            operations={selectedUnit.convert_from_standard}
            readOnly={
              !isGranted('akeneo_measurements_measurement_unit_edit') ||
              measurementFamily.is_locked ||
              selectedUnit.code === measurementFamily.standard_unit_code
            }
            onOperationsChange={(operations: Operation[]) =>
              onMeasurementFamilyChange(setUnitOperations(measurementFamily, selectedUnit.code, operations))
            }
            errors={filterErrors(errors, '[convert_from_standard]')}
          />
          <SectionTitle sticky={0}>
            <SectionTitle.Title>{translate('measurements.label_translations')}</SectionTitle.Title>
          </SectionTitle>
          {null !== locales &&
            locales.map(locale => (
              <TextField
                label={locale.label}
                key={locale.code}
                locale={locale.code}
                readOnly={!isGranted('akeneo_measurements_measurement_unit_edit')}
                value={selectedUnit.labels[locale.code] || ''}
                onChange={value =>
                  onMeasurementFamilyChange(setUnitLabel(measurementFamily, selectedUnitCode, locale.code, value))
                }
                errors={filterErrors(errors, `[labels][${locale.code}]`)}
              />
            ))}
        </Section>
        {isGranted('akeneo_measurements_measurement_unit_delete') &&
          !measurementFamily.is_locked &&
          selectedUnitCode !== measurementFamily.standard_unit_code && (
            <Footer>
              <Button level="danger" ghost={true} onClick={openConfirmDeleteUnitModal}>
                {translate('measurements.unit.delete.button')}
              </Button>
            </Footer>
          )}
      </Container>
    </>
  );
};

export {UnitDetails};
