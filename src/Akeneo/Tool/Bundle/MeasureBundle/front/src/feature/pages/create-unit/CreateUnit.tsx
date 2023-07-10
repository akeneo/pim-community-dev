import React, {useCallback, useContext, useRef, useState} from 'react';
import styled from 'styled-components';
import {
  Helper,
  MeasurementIllustration,
  Button,
  Modal,
  Checkbox,
  useAutoFocus,
  useShortcut,
  Key,
} from 'akeneo-design-system';
import {useForm} from '../../hooks/use-form';
import {getMeasurementFamilyLabel, MeasurementFamily} from '../../model/measurement-family';
import {Unit} from '../../model/unit';
import {
  CreateUnitForm,
  createUnitFromForm,
  initializeCreateUnitForm,
  validateCreateUnitForm,
} from '../create-unit/form/create-unit-form';
import {useCreateUnitValidator} from '../create-unit/hooks/use-create-unit-validator';
import {Operation} from '../../model/operation';
import {OperationCollection} from '../common/OperationCollection';
import {
  filterErrors,
  ValidationError,
  getErrorsForPath,
  TextField,
  Section,
  useTranslate,
  useNotify,
  NotificationLevel,
  useUserContext,
} from '@akeneo-pim-community/shared';
import {ConfigContext} from '../../context/config-context';

type CreateUnitProps = {
  measurementFamily: MeasurementFamily;
  onClose: () => void;
  onNewUnit: (unit: Unit) => void;
};

const FormGroup = styled(Section)`
  max-width: 400px;
`;

const CreateUnit = ({onClose, onNewUnit, measurementFamily}: CreateUnitProps) => {
  const translate = useTranslate();
  const notify = useNotify();
  const locale = useUserContext().get('uiLocale');
  const config = useContext(ConfigContext);

  const [form, setFormValue, clearForm] = useForm<CreateUnitForm>(initializeCreateUnitForm());
  const validate = useCreateUnitValidator();
  const [createAnotherUnit, setCreateAnotherUnit] = useState<boolean>(false);
  const handleClose = useCallback(() => {
    clearForm();
    onClose();
  }, [clearForm, onClose]);
  const [errors, setErrors] = useState<ValidationError[]>([]);
  const measurementFamilyLabel = getMeasurementFamilyLabel(measurementFamily, locale);

  const firstFieldRef = useRef<HTMLInputElement | null>(null);
  const focusFirstField = useAutoFocus(firstFieldRef);

  const handleAdd = useCallback(async () => {
    try {
      setErrors([]);

      const formValidationErrors = validateCreateUnitForm(form, measurementFamily, translate);
      if (0 < formValidationErrors.length) {
        setErrors(formValidationErrors);
        return;
      }

      const unit = createUnitFromForm(form, locale);
      const response = await validate(measurementFamily.code, unit);

      switch (response.valid) {
        case true:
          onNewUnit(unit);
          focusFirstField();
          createAnotherUnit ? clearForm() : handleClose();
          break;

        case false:
          setErrors(response.errors);
          break;
      }
    } catch (error) {
      console.error(error);
      notify(NotificationLevel.ERROR, translate('measurements.add_unit.flash.error'));
    }
  }, [
    form,
    locale,
    validate,
    measurementFamily,
    notify,
    onNewUnit,
    createAnotherUnit,
    clearForm,
    handleClose,
    setErrors,
    translate,
    focusFirstField,
  ]);

  useShortcut(Key.Enter, handleAdd);

  return (
    <Modal closeTitle={translate('pim_common.close')} onClose={handleClose} illustration={<MeasurementIllustration />}>
      <Modal.SectionTitle color="brand">
        {translate('measurements.title.measurement')} / {measurementFamilyLabel}
      </Modal.SectionTitle>
      <Modal.Title>{translate('measurements.unit.add_new')}</Modal.Title>
      <Section>
        {measurementFamily.is_locked && (
          <Helper level="warning">{translate('measurements.unit.will_be_read_only')}</Helper>
        )}
        <FormGroup>
          <TextField
            ref={firstFieldRef}
            label={translate('pim_common.code')}
            value={form.code}
            onChange={value => setFormValue('code', value)}
            required={true}
            errors={getErrorsForPath(errors, 'code')}
          />
          <TextField
            label={translate('pim_common.label')}
            value={form.label}
            onChange={value => setFormValue('label', value)}
            locale={locale}
            errors={getErrorsForPath(errors, `labels[${locale}]`)}
          />
          <TextField
            label={translate('measurements.form.input.symbol')}
            value={form.symbol}
            onChange={value => setFormValue('symbol', value)}
            errors={getErrorsForPath(errors, 'symbol')}
          />
          <OperationCollection
            operations={form.operations}
            onOperationsChange={(operations: Operation[]) => setFormValue('operations', operations)}
            errors={filterErrors(errors, `convert_from_standard`)}
          />
          <Checkbox
            id="measurements.unit.create_another"
            checked={createAnotherUnit}
            onChange={(checked: boolean) => setCreateAnotherUnit(checked)}
          >
            {translate('measurements.unit.create_another')}
          </Checkbox>
        </FormGroup>
      </Section>
      <Modal.BottomButtons>
        <Button onClick={handleAdd} disabled={config.units_max <= measurementFamily.units.length}>
          {translate('pim_common.add')}
        </Button>
      </Modal.BottomButtons>
    </Modal>
  );
};

export {CreateUnit};
