import React, {FormEvent, useCallback, useContext, useRef, useState} from 'react';
import {Helper, MeasurementIllustration, Button} from 'akeneo-design-system';
import {Subsection} from 'akeneomeasure/shared/components/Subsection';
import {TextField} from 'akeneomeasure/shared/components/TextField';
import {FormGroup} from 'akeneomeasure/shared/components/FormGroup';
import {useForm} from 'akeneomeasure/hooks/use-form';
import {getMeasurementFamilyLabel, MeasurementFamily} from 'akeneomeasure/model/measurement-family';
import {Unit} from 'akeneomeasure/model/unit';
import {
  CreateUnitForm,
  createUnitFromForm,
  initializeCreateUnitForm,
  validateCreateUnitForm,
} from 'akeneomeasure/pages/create-unit/form/create-unit-form';
import {useCreateUnitValidator} from 'akeneomeasure/pages/create-unit/hooks/use-create-unit-validator';
import {CheckboxField} from 'akeneomeasure/shared/components/CheckboxField';
import {Operation} from 'akeneomeasure/model/operation';
import {OperationCollection} from 'akeneomeasure/pages/common/OperationCollection';
import {ConfigContext} from 'akeneomeasure/context/config-context';
import {useTranslate, useNotify, NotificationLevel, useUserContext} from '@akeneo-pim-community/legacy-bridge';
import {
  useAutoFocus,
  filterErrors,
  ValidationError,
  useShortcut,
  Key,
  Modal,
  ModalBodyWithIllustration,
  ModalCloseButton,
  ModalTitle,
} from '@akeneo-pim-community/shared';

type CreateUnitProps = {
  measurementFamily: MeasurementFamily;
  onClose: () => void;
  onNewUnit: (unit: Unit) => void;
};

const CreateUnit = ({onClose, onNewUnit, measurementFamily}: CreateUnitProps) => {
  const __ = useTranslate();
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

      const formValidationErrors = validateCreateUnitForm(form, measurementFamily, __);
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
      notify(NotificationLevel.ERROR, __('measurements.add_unit.flash.error'));
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
    __,
  ]);

  useShortcut(Key.Escape, handleClose);
  useShortcut(Key.Enter, handleAdd);
  useShortcut(Key.NumpadEnter, handleAdd);

  return (
    <Modal>
      <ModalCloseButton title={__('pim_common.close')} onClick={handleClose} />
      <ModalBodyWithIllustration illustration={<MeasurementIllustration />}>
        <ModalTitle
          title={__('measurements.unit.add_new')}
          subtitle={`${__('measurements.title.measurement')} / ${measurementFamilyLabel}`}
        />
        <Subsection>
          {measurementFamily.is_locked && <Helper level="warning">{__('measurements.unit.will_be_read_only')}</Helper>}
          <FormGroup>
            <TextField
              ref={firstFieldRef}
              id="measurements.unit.create.code"
              label={__('pim_common.code')}
              value={form.code}
              onChange={(e: FormEvent<HTMLInputElement>) => setFormValue('code', e.currentTarget.value)}
              required={true}
              errors={errors.filter(error => error.propertyPath === 'code')}
            />
            <TextField
              id="measurements.unit.create.label"
              label={__('pim_common.label')}
              value={form.label}
              onChange={(e: FormEvent<HTMLInputElement>) => setFormValue('label', e.currentTarget.value)}
              flag={locale}
              errors={errors.filter(error => error.propertyPath === 'label')}
            />
            <TextField
              id="measurements.unit.create.symbol"
              label={__('measurements.form.input.symbol')}
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
              label={__('measurements.unit.create_another')}
              value={createAnotherUnit}
              onChange={(checked: boolean) => setCreateAnotherUnit(checked)}
            />
          </FormGroup>
        </Subsection>
        <Button onClick={handleAdd} disabled={config.units_max <= measurementFamily.units.length}>
          {__('pim_common.add')}
        </Button>
      </ModalBodyWithIllustration>
    </Modal>
  );
};

export {CreateUnit};
