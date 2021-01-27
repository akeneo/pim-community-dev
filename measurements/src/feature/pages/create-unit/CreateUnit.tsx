import React, {FormEvent, useCallback, useContext, useRef, useState} from 'react';
import {Helper, MeasurementIllustration, Button, SectionTitle, Title, Modal} from 'akeneo-design-system';
import {Subsection} from '../../shared/components/Subsection';
import {TextField} from '../../shared/components/TextField';
import {FormGroup} from '../../shared/components/FormGroup';
import {useForm} from '../../hooks/use-form';
import {getMeasurementFamilyLabel, MeasurementFamily} from '../../model/measurement-family';
import {Unit} from '../../model/unit';
import {
  CreateUnitForm,
  createUnitFromForm,
  initializeCreateUnitForm,
  validateCreateUnitForm,
} from '../../pages/create-unit/form/create-unit-form';
import {useCreateUnitValidator} from '../../pages/create-unit/hooks/use-create-unit-validator';
import {CheckboxField} from '../../shared/components/CheckboxField';
import {Operation} from '../../model/operation';
import {OperationCollection} from '../../pages/common/OperationCollection';
import {ConfigContext} from '../../context/config-context';
import {useTranslate, useNotify, NotificationLevel, useUserContext} from '@akeneo-pim-community/legacy';
import {useAutoFocus, filterErrors, ValidationError, useShortcut, Key} from '@akeneo-pim-community/shared';

type CreateUnitProps = {
  measurementFamily: MeasurementFamily;
  isOpen: boolean;
  onClose: () => void;
  onNewUnit: (unit: Unit) => void;
};

const CreateUnit = ({isOpen, onClose, onNewUnit, measurementFamily}: CreateUnitProps) => {
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
  useShortcut(Key.NumpadEnter, handleAdd);

  if (!isOpen) return null;

  return (
    <Modal closeTitle={translate('pim_common.close')} onClose={handleClose} illustration={<MeasurementIllustration />}>
      <SectionTitle color="brand">
        {translate('measurements.title.measurement')} / {measurementFamilyLabel}
      </SectionTitle>
      <Title>{translate('measurements.unit.add_new')}</Title>
      <Subsection>
        {measurementFamily.is_locked && (
          <Helper level="warning">{translate('measurements.unit.will_be_read_only')}</Helper>
        )}
        <FormGroup>
          <TextField
            ref={firstFieldRef}
            id="measurements.unit.create.code"
            label={translate('pim_common.code')}
            value={form.code}
            onChange={(e: FormEvent<HTMLInputElement>) => setFormValue('code', e.currentTarget.value)}
            required={true}
            errors={errors.filter(error => error.propertyPath === 'code')}
          />
          <TextField
            id="measurements.unit.create.label"
            label={translate('pim_common.label')}
            value={form.label}
            onChange={(e: FormEvent<HTMLInputElement>) => setFormValue('label', e.currentTarget.value)}
            flag={locale}
            errors={errors.filter(error => error.propertyPath === 'label')}
          />
          <TextField
            id="measurements.unit.create.symbol"
            label={translate('measurements.form.input.symbol')}
            value={form.symbol}
            onChange={(e: FormEvent<HTMLInputElement>) => setFormValue('symbol', e.currentTarget.value)}
            errors={errors.filter(error => error.propertyPath === 'symbol')}
          />
          <OperationCollection
            operations={form.operations}
            onOperationsChange={(operations: Operation[]) => setFormValue('operations', operations)}
            errors={filterErrors(errors, `convert_from_standard`)}
          />
          <CheckboxField
            id="measurements.unit.create_another"
            label={translate('measurements.unit.create_another')}
            value={createAnotherUnit}
            onChange={(checked: boolean) => setCreateAnotherUnit(checked)}
          />
        </FormGroup>
      </Subsection>
      <Modal.BottomButtons>
        <Button onClick={handleAdd} disabled={config.units_max <= measurementFamily.units.length}>
          {translate('pim_common.add')}
        </Button>
      </Modal.BottomButtons>
    </Modal>
  );
};

export {CreateUnit};
