import React, {FormEvent, useCallback, useContext, useState} from 'react';
import {Modal, ModalBodyWithIllustration, ModalCloseButton, ModalTitle} from 'akeneomeasure/shared/components/Modal';
import {TranslateContext} from 'akeneomeasure/context/translate-context';
import {NotificationLevel, NotifyContext} from 'akeneomeasure/context/notify-context';
import {UserContext} from 'akeneomeasure/context/user-context';
import {MeasurementFamilyIllustration} from 'akeneomeasure/shared/illustrations/MeasurementFamilyIllustration';
import {Subsection, SubsectionHeader} from 'akeneomeasure/shared/components/Subsection';
import {HelperLevel, HelperRibbon} from 'akeneomeasure/shared/components/HelperRibbon';
import {TextField} from 'akeneomeasure/shared/components/TextField';
import {FormGroup} from 'akeneomeasure/shared/components/FormGroup';
import {Button} from 'akeneomeasure/shared/components/Button';
import {useCreateMeasurementFamilySaver} from 'akeneomeasure/pages/create-measurement-family/hooks/use-create-measurement-family-saver';
import {
  CreateMeasurementFamilyForm,
  initializeCreateMeasurementFamilyForm,
  createMeasurementFamilyFromForm,
} from 'akeneomeasure/pages/create-measurement-family/form/create-measurement-family-form';
import {ValidationError, getErrorsForPath} from 'akeneomeasure/model/validation-error';
import {useShortcut} from 'akeneomeasure/shared/hooks/use-shortcut';
import {Key} from 'akeneomeasure/shared/key';
import {useForm} from 'akeneomeasure/hooks/use-form';
import {MeasurementFamilyCode} from 'akeneomeasure/model/measurement-family';

type CreateMeasurementFamilyProps = {
  onClose: (createdMeasurementFamilyCode?: MeasurementFamilyCode) => void;
};

const CreateMeasurementFamily = ({onClose}: CreateMeasurementFamilyProps) => {
  const __ = useContext(TranslateContext);
  const notify = useContext(NotifyContext);
  const locale = useContext(UserContext)('uiLocale');

  const [form, setFormValue] = useForm<CreateMeasurementFamilyForm>(initializeCreateMeasurementFamilyForm());
  const saveMeasurementFamily = useCreateMeasurementFamilySaver();
  const [errors, setErrors] = useState<ValidationError[]>([]);

  const handleClose = useCallback(onClose, [onClose]);

  useShortcut(Key.Escape, handleClose);

  const handleSave = useCallback(async () => {
    try {
      const measurementFamily = createMeasurementFamilyFromForm(form, locale);
      const response = await saveMeasurementFamily(measurementFamily);

      switch (response.success) {
        case true:
          notify(NotificationLevel.SUCCESS, __('measurements.create_family.flash.success'));
          handleClose(measurementFamily.code);
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
      <ModalCloseButton title={__('pim_common.close')} onClick={() => handleClose()} />
      <ModalBodyWithIllustration illustration={<MeasurementFamilyIllustration />}>
        <ModalTitle
          title={__('measurements.family.add_new_measurement_family')}
          subtitle={__('measurements.title.measurement')}
        />
        <Subsection>
          <SubsectionHeader>{__('pim_common.properties')}</SubsectionHeader>
          <FormGroup>
            <TextField
              id="measurements.measurement_family.create.family_code"
              label={__('pim_common.code')}
              value={form.family_code}
              onChange={(e: FormEvent<HTMLInputElement>) => setFormValue('family_code', e.currentTarget.value)}
              required={true}
              errors={getErrorsForPath(errors, 'code')}
            />
            <TextField
              id="measurements.measurement_family.create.family_label"
              label={__('pim_common.label')}
              value={form.family_label}
              onChange={(e: FormEvent<HTMLInputElement>) => setFormValue('family_label', e.currentTarget.value)}
              flag={locale}
              errors={getErrorsForPath(errors, `labels[${locale}]`)}
            />
          </FormGroup>
        </Subsection>
        <Subsection>
          <SubsectionHeader>{__('measurements.family.standard_unit')}</SubsectionHeader>
          <HelperRibbon level={HelperLevel.HELPER_LEVEL_WARNING}>
            {__('measurements.family.standard_unit_is_not_editable_after_creation')}
          </HelperRibbon>
          <FormGroup>
            <TextField
              id="measurements.measurement_family.create.standard_unit_code"
              label={__('pim_common.code')}
              value={form.standard_unit_code}
              onChange={(e: FormEvent<HTMLInputElement>) => setFormValue('standard_unit_code', e.currentTarget.value)}
              required={true}
              errors={getErrorsForPath(errors, 'units[0][code]')}
            />
            <TextField
              id="measurements.measurement_family.create.standard_unit_label"
              label={__('pim_common.label')}
              value={form.standard_unit_label}
              onChange={(e: FormEvent<HTMLInputElement>) => setFormValue('standard_unit_label', e.currentTarget.value)}
              flag={locale}
              errors={getErrorsForPath(errors, `units[0][labels][${locale}]`)}
            />
            <TextField
              id="measurements.measurement_family.create.standard_unit_symbol"
              label={__('measurements.form.input.symbol')}
              value={form.standard_unit_symbol}
              onChange={(e: FormEvent<HTMLInputElement>) => setFormValue('standard_unit_symbol', e.currentTarget.value)}
              errors={getErrorsForPath(errors, 'units[0][symbol]')}
            />
          </FormGroup>
        </Subsection>
        <Button onClick={handleSave}>{__('pim_common.save')}</Button>
      </ModalBodyWithIllustration>
    </Modal>
  );
};

export {CreateMeasurementFamily};
