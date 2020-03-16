import React, {FormEvent, useCallback, useContext, useState} from 'react';
import {Modal, ModalBodyWithIllustration, ModalCloseButton, ModalTitle} from 'akeneomeasure/shared/components/Modal';
import {TranslateContext} from 'akeneomeasure/context/translate-context';
import {NotificationLevel, NotifyContext} from 'akeneomeasure/context/notify-context';
import {UserContext} from 'akeneomeasure/context/user-context';
import {MeasurementFamilyIllustration} from 'akeneomeasure/shared/illustrations/MeasurementFamilyIllustration';
import {Subsection, SubsectionHeader} from 'akeneomeasure/shared/components/Subsection';
import {HELPER_LEVEL_WARNING, SubsectionHelper} from 'akeneomeasure/shared/components/SubsectionHelper';
import {TextField} from 'akeneomeasure/shared/components/TextField';
import {FormGroup} from 'akeneomeasure/shared/components/FormGroup';
import {Button} from 'akeneomeasure/shared/components/Button';
import {useCreateMeasurementFamilyState} from 'akeneomeasure/pages/create-measurement-family/hooks/use-create-measurement-family-state';
import {useCreateMeasurementFamilySaver} from 'akeneomeasure/pages/create-measurement-family/hooks/use-create-measurement-family-saver';
import {createMeasurementFamilyFromFormState} from 'akeneomeasure/pages/create-measurement-family/form/create-measurement-family-form';
import {ValidationError} from 'akeneomeasure/model/validation-error';
import {useShortcut} from 'akeneomeasure/shared/hooks/use-shortcut';
import {Key} from 'akeneomeasure/shared/key';

type CreateMeasurementFamilyProps = {
  onClose: () => void;
};

const CreateMeasurementFamily = ({onClose}: CreateMeasurementFamilyProps) => {
  const __ = useContext(TranslateContext);
  const notify = useContext(NotifyContext);
  const locale = useContext(UserContext)('uiLocale');

  const [form, setFieldValue] = useCreateMeasurementFamilyState();
  const saveMeasurementFamily = useCreateMeasurementFamilySaver();
  const [errors, setErrors] = useState<ValidationError[]>([]);

  const handleClose = useCallback(onClose, [onClose]);

  useShortcut(Key.Escape, handleClose);

  const handleSave = useCallback(async () => {
    try {
      const measurementFamily = createMeasurementFamilyFromFormState(form, locale);
      const response = await saveMeasurementFamily(measurementFamily);

      switch (response.success) {
        case true:
          notify(NotificationLevel.SUCCESS, __('measurements.create_family.flash.success'));
          handleClose();
          break;

        case false:
          setErrors(response.errors);
          break;
      }
    } catch (error) {
      console.error(error);
      notify(NotificationLevel.ERROR, __('measurements.create_family.flash.error'));
    }
  }, [form, locale, saveMeasurementFamily, notify, __, handleClose, setErrors]);

  return (
    <Modal>
      <ModalCloseButton title={__('close')} onClick={handleClose} />
      <ModalBodyWithIllustration illustration={<MeasurementFamilyIllustration />}>
        <ModalTitle
          title={__('measurements.family.add_new_measurement_family')}
          subtitle={__('measurements.title.measurement')}
        />
        <Subsection>
          <SubsectionHeader>{__('measurements.family.properties')}</SubsectionHeader>
          <FormGroup>
            <TextField
              id="measurements.measurement_family.create.family_code"
              label={__('measurements.form.input.code')}
              value={form.family_code}
              onChange={(e: FormEvent<HTMLInputElement>) => setFieldValue('family_code', e.currentTarget.value)}
              required={true}
              errors={errors.filter(error => error.property === 'code')}
            />
            <TextField
              id="measurements.measurement_family.create.family_label"
              label={__('measurements.form.input.label')}
              value={form.family_label}
              onChange={(e: FormEvent<HTMLInputElement>) => setFieldValue('family_label', e.currentTarget.value)}
              flag={locale}
              errors={errors.filter(error => error.property === 'labels')}
            />
          </FormGroup>
        </Subsection>
        <Subsection>
          <SubsectionHeader>{__('measurements.family.standard_unit')}</SubsectionHeader>
          <SubsectionHelper level={HELPER_LEVEL_WARNING}>
            {__('measurements.family.standard_unit_is_not_editable_after_creation')}
          </SubsectionHelper>
          <FormGroup>
            <TextField
              id="measurements.measurement_family.create.standard_unit_code"
              label={__('measurements.form.input.code')}
              value={form.standard_unit_code}
              onChange={(e: FormEvent<HTMLInputElement>) => setFieldValue('standard_unit_code', e.currentTarget.value)}
              required={true}
              errors={errors.filter(error => error.property === 'units[0][code]')}
            />
            <TextField
              id="measurements.measurement_family.create.standard_unit_label"
              label={__('measurements.form.input.label')}
              value={form.standard_unit_label}
              onChange={(e: FormEvent<HTMLInputElement>) => setFieldValue('standard_unit_label', e.currentTarget.value)}
              flag={locale}
              errors={errors.filter(error => error.property === 'units[0][labels]')}
            />
            <TextField
              id="measurements.measurement_family.create.standard_unit_symbol"
              label={__('measurements.form.input.symbol')}
              value={form.standard_unit_symbol}
              onChange={(e: FormEvent<HTMLInputElement>) => setFieldValue('standard_unit_symbol', e.currentTarget.value)}
              errors={errors.filter(error => error.property === 'units[0][symbol]')}
            />
          </FormGroup>
        </Subsection>
        <Button onClick={handleSave}>{__('measurements.form.save')}</Button>
      </ModalBodyWithIllustration>
    </Modal>
  );
};

export {CreateMeasurementFamily};
