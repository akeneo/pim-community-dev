import React, {useCallback} from 'react';
import styled from 'styled-components';
import {SubsectionHeader} from 'akeneomeasure/shared/components/Subsection';
import {FormGroup} from 'akeneomeasure/shared/components/FormGroup';
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
import {ConfirmDeleteModal} from 'akeneomeasure/shared/components/ConfirmDeleteModal';
import {useTranslate, useUserContext, useSecurity} from '@akeneo-pim-community/legacy-bridge';
import {filterErrors, ValidationError, useToggleState, inputErrors} from '@akeneo-pim-community/shared';
import {Button, Field, TextInput} from 'akeneo-design-system';

const Container = styled.div`
  margin-left: 40px;
  width: 400px;
  overflow: auto;
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
      <ConfirmDeleteModal
        isOpen={isConfirmDeleteUnitModalOpen}
        description={translate('measurements.unit.delete.confirm')}
        onConfirm={handleRemoveUnit}
        onCancel={closeConfirmDeleteUnitModal}
      />
      <Container>
        <SubsectionHeader top={0}>
          {translate('measurements.unit.title', {unitLabel: getUnitLabel(selectedUnit, locale)})}
        </SubsectionHeader>
        <FormGroup>
          <Field label={`${translate('pim_common.code')} ${translate('pim_common.required_label')}`}>
            <TextInput id="measurements.unit.properties.code" value={selectedUnit.code} readOnly={true} />
            {inputErrors(translate, filterErrors(errors, '[code]'))}
          </Field>
          <Field label={translate('measurements.unit.symbol')}>
            <TextInput
              id="measurements.unit.properties.symbol"
              value={selectedUnit.symbol}
              readOnly={!isGranted('akeneo_measurements_measurement_unit_edit')}
              onChange={(value: string) =>
                onMeasurementFamilyChange(setUnitSymbol(measurementFamily, selectedUnit.code, value))
              }
            />
            {inputErrors(translate, filterErrors(errors, '[symbol]'))}
          </Field>
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
        </FormGroup>
        <SubsectionHeader top={0}>{translate('measurements.label_translations')}</SubsectionHeader>
        <FormGroup>
          {null !== locales &&
            locales.map(locale => (
              <Field key={locale.code} label={locale.label} locale={locale.code}>
                <TextInput
                  id={`measurements.family.properties.label.${locale.code}`}
                  readOnly={!isGranted('akeneo_measurements_measurement_unit_edit')}
                  value={selectedUnit.labels[locale.code] || ''}
                  onChange={(value: string) =>
                    onMeasurementFamilyChange(setUnitLabel(measurementFamily, selectedUnitCode, locale.code, value))
                  }
                />
                {inputErrors(translate, filterErrors(errors, `[labels][${locale.code}]`))}
              </Field>
            ))}
        </FormGroup>
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
