import React, {useCallback, useState} from 'react';
import {Helper, MeasurementIllustration, Button, Modal, SectionTitle} from 'akeneo-design-system';
import {useCreateMeasurementFamilySaver} from 'akeneomeasure/pages/create-measurement-family/hooks/use-create-measurement-family-saver';
import {
  CreateMeasurementFamilyForm,
  initializeCreateMeasurementFamilyForm,
  createMeasurementFamilyFromForm,
} from 'akeneomeasure/pages/create-measurement-family/form/create-measurement-family-form';
import {useForm} from 'akeneomeasure/hooks/use-form';
import {MeasurementFamilyCode} from 'akeneomeasure/model/measurement-family';
import {useTranslate, useNotify, NotificationLevel, useUserContext} from '@akeneo-pim-community/legacy-bridge';
import {ValidationError, getErrorsForPath, TextField, Section} from '@akeneo-pim-community/shared';

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
      <Modal.SectionTitle color="brand">{translate('measurements.title.measurement')}</Modal.SectionTitle>
      <Modal.Title>{translate('measurements.family.add_new_measurement_family')}</Modal.Title>
      <Section>
        <SectionTitle>
          <SectionTitle.Title>{translate('pim_common.properties')}</SectionTitle.Title>
        </SectionTitle>
        <TextField
          label={translate('pim_common.code')}
          value={form.family_code}
          onChange={value => setFormValue('family_code', value)}
          required={true}
          errors={getErrorsForPath(errors, 'code')}
        />
        <TextField
          label={translate('pim_common.label')}
          value={form.family_label}
          onChange={value => setFormValue('family_label', value)}
          locale={locale}
          errors={getErrorsForPath(errors, `labels[${locale}]`)}
        />
        <div>
          <SectionTitle>
            <SectionTitle.Title>{translate('measurements.family.standard_unit')}</SectionTitle.Title>
          </SectionTitle>
          <Helper level="warning">
            {translate('measurements.family.standard_unit_is_not_editable_after_creation')}
          </Helper>
        </div>
        <TextField
          label={translate('pim_common.code')}
          value={form.standard_unit_code}
          onChange={value => setFormValue('standard_unit_code', value)}
          required={true}
          errors={getErrorsForPath(errors, 'units[0][code]')}
        />
        <TextField
          label={translate('pim_common.label')}
          value={form.standard_unit_label}
          onChange={value => setFormValue('standard_unit_label', value)}
          locale={locale}
          errors={getErrorsForPath(errors, `units[0][labels][${locale}]`)}
        />
        <TextField
          label={translate('measurements.form.input.symbol')}
          value={form.standard_unit_symbol}
          onChange={value => setFormValue('standard_unit_symbol', value)}
          errors={getErrorsForPath(errors, 'units[0][symbol]')}
        />
      </Section>
      <Modal.BottomButtons>
        <Button onClick={handleSave}>{translate('pim_common.save')}</Button>
      </Modal.BottomButtons>
    </Modal>
  );
};

export {CreateMeasurementFamily};
