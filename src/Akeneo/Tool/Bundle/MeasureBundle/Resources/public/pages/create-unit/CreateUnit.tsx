import React, {useCallback, useContext, useRef, useState} from 'react';
import {
  Helper,
  MeasurementIllustration,
  Button,
  SectionTitle,
  Title,
  Modal,
  TextInput,
  Field,
  Checkbox,
} from 'akeneo-design-system';
import {Subsection} from 'akeneomeasure/shared/components/Subsection';
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
  inputErrors,
  getErrorsForPath,
} from '@akeneo-pim-community/shared';

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
          <Field label={`${translate('pim_common.code')} ${translate('pim_common.required_label')}`}>
            <TextInput
              ref={firstFieldRef}
              id="measurements.unit.create.code"
              value={form.code}
              onChange={(value: string) => setFormValue('code', value)}
            />
            {inputErrors(translate, getErrorsForPath(errors, 'code'))}
          </Field>
          <Field label={translate('pim_common.label')} locale={locale}>
            <TextInput
              id="measurements.unit.create.label"
              value={form.label}
              onChange={(value: string) => setFormValue('label', value)}
            />
            {inputErrors(translate, getErrorsForPath(errors, 'label'))}
          </Field>
          <Field label={translate('measurements.form.input.symbol')}>
            <TextInput
              id="measurements.unit.create.symbol"
              value={form.symbol}
              onChange={(value: string) => setFormValue('symbol', value)}
            />
            {inputErrors(translate, getErrorsForPath(errors, 'symbol'))}
          </Field>
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
