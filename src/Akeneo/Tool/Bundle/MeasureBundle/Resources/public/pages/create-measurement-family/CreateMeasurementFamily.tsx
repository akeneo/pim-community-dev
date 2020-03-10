import React, {useCallback, useContext} from 'react';
import {Modal, ModalHeader, ModalBodyWithIllustration} from 'akeneomeasure/shared/components/Modal';
import {CloseButton} from 'akeneomeasure/shared/components/CloseButton';
import {TranslateContext} from 'akeneomeasure/shared/translate/translate-context';
import {MeasureFamilyIllustration} from 'akeneomeasure/shared/illustrations/MeasureFamilyIllustration';

type CreateMeasurementFamilyProps = {
  onClose: () => void;
};

export const CreateMeasurementFamily = ({onClose}: CreateMeasurementFamilyProps) => {
  const __ = useContext(TranslateContext);
  const handleClose = useCallback(() => {
    onClose();
  }, []);

  return (
    <Modal>
      <ModalHeader>
        <CloseButton title={__('measurements.close')} onClick={handleClose}/>
      </ModalHeader>
      <ModalBodyWithIllustration illustration={<MeasureFamilyIllustration/>}>
        <div>Hello World</div>
      </ModalBodyWithIllustration>
    </Modal>
  );
};
