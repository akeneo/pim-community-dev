import React, {FormEvent, useCallback, useContext, useState} from 'react';
import {Modal, ModalBodyWithIllustration, ModalCloseButton, ModalTitle} from 'akeneomeasure/shared/components/Modal';
import {TranslateContext} from 'akeneomeasure/context/translate-context';
import {UserContext} from 'akeneomeasure/context/user-context';
import {MeasurementFamilyIllustration} from 'akeneomeasure/shared/illustrations/MeasurementFamilyIllustration';
import {Subsection} from 'akeneomeasure/shared/components/Subsection';
import {HELPER_LEVEL_WARNING, SubsectionHelper} from 'akeneomeasure/shared/components/SubsectionHelper';
import {TextField} from 'akeneomeasure/shared/components/TextField';
import {FormGroup} from 'akeneomeasure/shared/components/FormGroup';
import {Button} from 'akeneomeasure/shared/components/Button';
import {useForm} from 'akeneomeasure/hooks/use-form';
import {useShortcut} from 'akeneomeasure/shared/hooks/use-shortcut';
import {Key} from 'akeneomeasure/shared/key';
import {getMeasurementFamilyLabel, MeasurementFamily, Unit} from 'akeneomeasure/model/measurement-family';
import {ValidationError} from 'akeneomeasure/model/validation-error';
import {
  CreateUnitForm,
  createUnitFromForm,
  initializeCreateUnitForm
} from 'akeneomeasure/pages/create-unit/form/create-unit-form';
import {useCreateUnitValidator} from 'akeneomeasure/pages/create-unit/hooks/use-create-unit-validator';
import {CheckboxField} from 'akeneomeasure/shared/components/CheckboxField';
import {NotificationLevel, NotifyContext} from 'akeneomeasure/context/notify-context';

type CreateUnitProps = {
  measurementFamily: MeasurementFamily;
  onClose: () => void;
  onNewUnit: (unit: Unit) => void;
};

const CreateUnit = ({
  onClose,
  onNewUnit,
  measurementFamily,
}: CreateUnitProps) => {
  const __ = useContext(TranslateContext);
  const notify = useContext(NotifyContext);
  const locale = useContext(UserContext)('uiLocale');

  const [form, setFormValue, clearForm] = useForm<CreateUnitForm>(initializeCreateUnitForm());
  const validate = useCreateUnitValidator();
  const [createAnotherUnit, setCreateAnotherUnit] = useState<boolean>(false);
  const [isReadOnly, setReadOnly] = useState<boolean>(false);
  const handleClose = useCallback(onClose, [onClose]);
  const [errors, setErrors] = useState<ValidationError[]>([]);
  const measurementFamilyLabel = getMeasurementFamilyLabel(measurementFamily, locale);

  // @TODO
  const measurementFamilyIsAlreadyUsed = true;

  const handleSuccess = useCallback((unit: Unit) => {
    onNewUnit(unit);

    if (createAnotherUnit) {
      clearForm();
    } else {
      handleClose();
    }
  }, [
    onNewUnit,
    createAnotherUnit,
    clearForm,
    handleClose,
  ]);

  const handleAdd = useCallback(async () => {
    try {
      setReadOnly(true);

      const unit = createUnitFromForm(form, locale);
      const response = await validate(unit);

      switch (response.valid) {
        case true:
          notify(NotificationLevel.SUCCESS, __('measurements.add_unit.flash.success'));
          handleSuccess(unit);
          break;

        case false:
          setErrors(response.errors);
          break;
      }
    } catch (error) {
      console.error(error);
      notify(NotificationLevel.ERROR, __('measurements.add_unit.flash.error'));
    } finally {
      setReadOnly(false);
    }
  }, [
    form,
    locale,
    validate,
    notify,
    handleSuccess,
    setErrors,
    setReadOnly,
  ]);

  useShortcut(Key.Escape, handleClose);

  return (
    <Modal>
      <ModalCloseButton title={__('close')} onClick={handleClose}/>
      <ModalBodyWithIllustration illustration={<MeasurementFamilyIllustration/>}>
        <ModalTitle
          title={__('measurements.family.add_new_unit')}
          subtitle={`${__('measurements.title.measurement')} / ${measurementFamilyLabel}`}
        />
        <Subsection>
          {measurementFamilyIsAlreadyUsed &&
            <SubsectionHelper level={HELPER_LEVEL_WARNING}>
              {__('measurements.family.is_already_used_and_this_new_unit_will_be_read_only_after_creation')}
            </SubsectionHelper>
          }
          <FormGroup>
            <TextField
              id="measurements.unit.create.code"
              label={__('measurements.form.input.code')}
              value={form.code}
              onChange={(e: FormEvent<HTMLInputElement>) => setFormValue('code', e.currentTarget.value)}
              required={true}
              readOnly={isReadOnly}
              errors={errors.filter(error => error.property === 'code')}
            />
            <TextField
              id="measurements.unit.create.label"
              label={__('measurements.form.input.label')}
              value={form.label}
              onChange={(e: FormEvent<HTMLInputElement>) => setFormValue('label', e.currentTarget.value)}
              flag={locale}
              readOnly={isReadOnly}
              errors={errors.filter(error => error.property === 'label')}
            />
            <TextField
              id="measurements.unit.create.symbol"
              label={__('measurements.form.input.symbol')}
              value={form.symbol}
              onChange={(e: FormEvent<HTMLInputElement>) => setFormValue('symbol', e.currentTarget.value)}
              readOnly={isReadOnly}
              errors={errors.filter(error => error.property === 'symbol')}
            />
            <CheckboxField
              id="measurements.unit.create_another"
              label={__('measurements.family.create_another_unit')}
              value={createAnotherUnit}
              readOnly={isReadOnly}
              onChange={(checked: boolean) => setCreateAnotherUnit(checked)}
            />
          </FormGroup>
        </Subsection>
        <Button onClick={handleAdd}>{__('measurements.form.add')}</Button>
      </ModalBodyWithIllustration>
    </Modal>
  );
};

export {
  CreateUnit
};
