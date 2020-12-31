import React, {useCallback, useState} from 'react';
import {
  Helper,
  MeasurementIllustration,
  Button,
  Modal,
  Title,
  SectionTitle,
  TextInput,
  Field,
} from 'akeneo-design-system';
import {Subsection, SubsectionHeader} from 'akeneomeasure/shared/components/Subsection';
import {FormGroup} from 'akeneomeasure/shared/components/FormGroup';
import {useCreateMeasurementFamilySaver} from 'akeneomeasure/pages/create-measurement-family/hooks/use-create-measurement-family-saver';
import {
  CreateMeasurementFamilyForm,
  initializeCreateMeasurementFamilyForm,
  createMeasurementFamilyFromForm,
} from 'akeneomeasure/pages/create-measurement-family/form/create-measurement-family-form';
import {useForm} from 'akeneomeasure/hooks/use-form';
import {MeasurementFamilyCode} from 'akeneomeasure/model/measurement-family';
import {useTranslate, useNotify, NotificationLevel, useUserContext} from '@akeneo-pim-community/legacy-bridge';
import {ValidationError, getErrorsForPath, inputErrors} from '@akeneo-pim-community/shared';

type CreateMeasurementFamilyProps = {
  isOpen: boolean;
  onClose: (createdMeasurementFamilyCode?: MeasurementFamilyCode) => void;
};

const CreateMeasurementFamily = ({isOpen, onClose}: CreateMeasurementFamilyProps) => {
  const translate = useTranslate();
  const notify = useNotify();
  const locale = useUserContext().get('uiLocale');

  const [form, setFormValue] = useForm<CreateMeasurementFamilyForm>(initializeCreateMeasurementFamilyForm());
  const saveMeasurementFamily = useCreateMeasurementFamilySaver();
  const [errors, setErrors] = useState<ValidationError[]>([]);

  const handleClose = useCallback(onClose, [onClose]);
  const handleSave = useCallback(async () => {
    try {
      const measurementFamily = createMeasurementFamilyFromForm(form, locale);
      const response = await saveMeasurementFamily(measurementFamily);

      switch (response.success) {
        case true:
          notify(NotificationLevel.SUCCESS, translate('measurements.create_family.flash.success'));
          handleClose(measurementFamily.code);
          break;

        case false:
          setErrors(response.errors);
          break;
      }
    } catch (error) {
      console.error(error);
      notify(NotificationLevel.ERROR, translate('measurements.create_family.flash.error'));
    }
  }, [form, locale, saveMeasurementFamily, notify, translate, handleClose, setErrors]);

  if (!isOpen) return null;

  return (
    <Modal
      closeTitle={translate('pim_common.close')}
      onClose={() => handleClose()}
      illustration={<MeasurementIllustration />}
    >
      <SectionTitle color="brand">{translate('measurements.title.measurement')}</SectionTitle>
      <Title>{translate('measurements.family.add_new_measurement_family')}</Title>
      <Subsection>
        <SubsectionHeader>{translate('pim_common.properties')}</SubsectionHeader>
        <FormGroup>
          <Field label={`${translate('pim_common.code')} ${translate('pim_common.required_label')}`}>
            <TextInput
              id="measurements.measurement_family.create.family_code"
              value={form.family_code}
              onChange={(value: string) => setFormValue('family_code', value)}
            />
            {inputErrors(translate, getErrorsForPath(errors, 'code'))}
          </Field>
          <Field label={translate('pim_common.label')} locale={locale}>
            <TextInput
              id="measurements.measurement_family.create.family_label"
              value={form.family_label}
              onChange={(value: string) => setFormValue('family_label', value)}
            />
            {inputErrors(translate, getErrorsForPath(errors, `labels[${locale}]`))}
          </Field>
        </FormGroup>
      </Subsection>
      <Subsection>
        <SubsectionHeader>{translate('measurements.family.standard_unit')}</SubsectionHeader>
        <Helper level="warning">{translate('measurements.family.standard_unit_is_not_editable_after_creation')}</Helper>
        <FormGroup>
          <Field label={`${translate('pim_common.code')} ${translate('pim_common.required_label')}`}>
            <TextInput
              id="measurements.measurement_family.create.standard_unit_code"
              value={form.standard_unit_code}
              onChange={(value: string) => setFormValue('standard_unit_code', value)}
            />
            {inputErrors(translate, getErrorsForPath(errors, 'units[0][code]'))}
          </Field>
          <Field label={translate('pim_common.label')} locale={locale}>
            <TextInput
              id="measurements.measurement_family.create.standard_unit_label"
              value={form.standard_unit_label}
              onChange={(value: string) => setFormValue('standard_unit_label', value)}
            />
            {inputErrors(translate, getErrorsForPath(errors, `units[0][labels][${locale}]`))}
          </Field>
          <Field label={translate('measurements.form.input.symbol')}>
            <TextInput
              id="measurements.measurement_family.create.standard_unit_symbol"
              value={form.standard_unit_symbol}
              onChange={(value: string) => setFormValue('standard_unit_symbol', value)}
            />
            {inputErrors(translate, getErrorsForPath(errors, 'units[0][symbol]'))}
          </Field>
        </FormGroup>
      </Subsection>
      <Modal.BottomButtons>
        <Button onClick={handleSave}>{translate('pim_common.save')}</Button>
      </Modal.BottomButtons>
    </Modal>
  );
};

export {CreateMeasurementFamily};
