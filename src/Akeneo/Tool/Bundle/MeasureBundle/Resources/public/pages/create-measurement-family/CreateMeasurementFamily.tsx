import React, {useCallback, useContext} from 'react';
import {Modal, ModalBodyWithIllustration, ModalCloseButton, ModalTitle} from 'akeneomeasure/shared/components/Modal';
import {TranslateContext} from 'akeneomeasure/shared/translate/translate-context';
import {MeasureFamilyIllustration} from 'akeneomeasure/shared/illustrations/MeasureFamilyIllustration';

type CreateMeasurementFamilyProps = {
  onClose: () => void;
};

export const CreateMeasurementFamily = ({onClose}: CreateMeasurementFamilyProps) => {
  const __ = useContext(TranslateContext);
  const handleClose = useCallback(() => {
    onClose();
  }, [onClose]);

  return (
    <Modal>
      <ModalCloseButton title={__('measurements.close')} onClick={handleClose}/>
      <ModalBodyWithIllustration illustration={<MeasureFamilyIllustration/>}>
        <ModalTitle
          title={__('measurements.add_new_measurement_family')}
          subtitle={__('measurements.title.measurement')}
        />
      </ModalBodyWithIllustration>
    </Modal>
  );
};
