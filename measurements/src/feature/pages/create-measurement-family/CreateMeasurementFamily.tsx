import React, {FormEvent, useCallback, useState} from 'react';
import {Helper, MeasurementIllustration, Button, Modal, Title, SectionTitle} from 'akeneo-design-system';
import {Subsection, SubsectionHeader} from '../../shared/components/Subsection';
import {TextField} from '../../shared/components/TextField';
import {FormGroup} from '../../shared/components/FormGroup';
import {useCreateMeasurementFamilySaver} from '../../pages/create-measurement-family/hooks/use-create-measurement-family-saver';
import {
  CreateMeasurementFamilyForm,
  initializeCreateMeasurementFamilyForm,
  createMeasurementFamilyFromForm,
} from '../../pages/create-measurement-family/form/create-measurement-family-form';
import {useForm} from '../../hooks/use-form';
import {MeasurementFamilyCode} from '../../model/measurement-family';
import {useTranslate, useNotify, NotificationLevel, useUserContext} from '@akeneo-pim-community/legacy';
import {ValidationError, getErrorsForPath} from '@akeneo-pim-community/shared';

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
          <TextField
            id="measurements.measurement_family.create.family_code"
            label={translate('pim_common.code')}
            value={form.family_code}
            onChange={(e: FormEvent<HTMLInputElement>) => setFormValue('family_code', e.currentTarget.value)}
            required={true}
            errors={getErrorsForPath(errors, 'code')}
          />
          <TextField
            id="measurements.measurement_family.create.family_label"
            label={translate('pim_common.label')}
            value={form.family_label}
            onChange={(e: FormEvent<HTMLInputElement>) => setFormValue('family_label', e.currentTarget.value)}
            flag={locale}
            errors={getErrorsForPath(errors, `labels[${locale}]`)}
          />
        </FormGroup>
      </Subsection>
      <Subsection>
        <SubsectionHeader>{translate('measurements.family.standard_unit')}</SubsectionHeader>
        <Helper level="warning">{translate('measurements.family.standard_unit_is_not_editable_after_creation')}</Helper>
        <FormGroup>
          <TextField
            id="measurements.measurement_family.create.standard_unit_code"
            label={translate('pim_common.code')}
            value={form.standard_unit_code}
            onChange={(e: FormEvent<HTMLInputElement>) => setFormValue('standard_unit_code', e.currentTarget.value)}
            required={true}
            errors={getErrorsForPath(errors, 'units[0][code]')}
          />
          <TextField
            id="measurements.measurement_family.create.standard_unit_label"
            label={translate('pim_common.label')}
            value={form.standard_unit_label}
            onChange={(e: FormEvent<HTMLInputElement>) => setFormValue('standard_unit_label', e.currentTarget.value)}
            flag={locale}
            errors={getErrorsForPath(errors, `units[0][labels][${locale}]`)}
          />
          <TextField
            id="measurements.measurement_family.create.standard_unit_symbol"
            label={translate('measurements.form.input.symbol')}
            value={form.standard_unit_symbol}
            onChange={(e: FormEvent<HTMLInputElement>) => setFormValue('standard_unit_symbol', e.currentTarget.value)}
            errors={getErrorsForPath(errors, 'units[0][symbol]')}
          />
        </FormGroup>
      </Subsection>
      <Modal.BottomButtons>
        <Button onClick={handleSave}>{translate('pim_common.save')}</Button>
      </Modal.BottomButtons>
    </Modal>
  );
};

export {CreateMeasurementFamily};
