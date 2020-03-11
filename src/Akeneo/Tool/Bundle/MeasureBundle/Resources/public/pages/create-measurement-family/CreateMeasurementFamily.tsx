import React, {FormEvent, useCallback, useContext} from 'react';
import {Modal, ModalBodyWithIllustration, ModalCloseButton, ModalTitle} from 'akeneomeasure/shared/components/Modal';
import {TranslateContext} from 'akeneomeasure/shared/translate/translate-context';
import {MeasureFamilyIllustration} from 'akeneomeasure/shared/illustrations/MeasureFamilyIllustration';
import {Subsection, SubsectionHeader} from 'akeneomeasure/shared/components/Subsection';
import {SubsectionHelper, HELPER_LEVEL_WARNING} from 'akeneomeasure/shared/components/SubsectionHelper';
import {InputText} from 'akeneomeasure/shared/components/InputText';
import {FormGroup} from 'akeneomeasure/shared/components/FormGroup';
import {Button} from 'akeneomeasure/shared/components/Button';
import {useCreateMeasurementFamilyState} from 'akeneomeasure/pages/create-measurement-family/hooks/useCreateMeasurementFamilyState';

type CreateMeasurementFamilyProps = {
  onClose: () => void;
};

export const CreateMeasurementFamily = ({onClose}: CreateMeasurementFamilyProps) => {
  const __ = useContext(TranslateContext);
  const locale = 'en_US'; // @todo load from user context

  const [fields, setFieldValue] = useCreateMeasurementFamilyState();

  const handleClose = useCallback(() => {
    onClose();
  }, [onClose]);
  const handleSave = useCallback(() => {
    // @todo
  }, [fields]);

  return (
    <Modal>
      <ModalCloseButton title={__('measurements.close')} onClick={handleClose}/>
      <ModalBodyWithIllustration illustration={<MeasureFamilyIllustration/>}>
        <ModalTitle
          title={__('measurements.add_new_measurement_family')}
          subtitle={__('measurements.title.measurement')}
        />
        <Subsection>
          <SubsectionHeader>{__('measurements.family.properties')}</SubsectionHeader>
        </Subsection>
        <FormGroup>
          <InputText
            id="measurements.create_measurement_family.family_code"
            label={__('measurements.form.input.code')}
            value={fields.family_code}
            onChange={(e: FormEvent<HTMLInputElement>) => setFieldValue('family_code', e.currentTarget.value)}
            required={true}
          />
          <InputText
            id="measurements.create_measurement_family.family_label"
            label={__('measurements.form.input.label')}
            value={fields.family_label}
            onChange={(e: FormEvent<HTMLInputElement>) => setFieldValue('family_label', e.currentTarget.value)}
            flag={locale}
          />
        </FormGroup>
        <Subsection>
          <SubsectionHeader>{__('measurements.family.standard_unit')}</SubsectionHeader>
        </Subsection>
        <SubsectionHelper level={HELPER_LEVEL_WARNING}>
          {__('measurements.family.standard_unit_is_not_editable_after_creation')}
        </SubsectionHelper>
        <FormGroup>
          <InputText
            id="measurements.create_measurement_family.standard_unit_code"
            label={__('measurements.form.input.code')}
            value={fields.standard_unit_code}
            onChange={(e: FormEvent<HTMLInputElement>) => setFieldValue('standard_unit_code', e.currentTarget.value)}
            required={true}
          />
          <InputText
            id="measurements.create_measurement_family.standard_unit_label"
            label={__('measurements.form.input.label')}
            value={fields.standard_unit_label}
            onChange={(e: FormEvent<HTMLInputElement>) => setFieldValue('standard_unit_label', e.currentTarget.value)}
            flag={locale}
          />
          <InputText
            id="measurements.create_measurement_family.standard_unit_symbol"
            label={__('measurements.form.input.symbol')}
            value={fields.standard_unit_symbol}
            onChange={(e: FormEvent<HTMLInputElement>) => setFieldValue('standard_unit_symbol', e.currentTarget.value)}
          />
        </FormGroup>
        <Button classNames={['AknButton--apply']} onClick={handleSave}>{__('measurements.form.save')}</Button>
      </ModalBodyWithIllustration>
    </Modal>
  );
};
