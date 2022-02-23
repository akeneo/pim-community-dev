import React from 'react';
import {Button, Modal, useBooleanState} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';

type CreateSupplierProps = {
    onSupplierCreated: () => void;
};

const CreateSupplier = ({onSupplierCreated}: CreateSupplierProps) => {
    const [isOpen, openModal, closeModal] = useBooleanState(false);
    const translate = useTranslate();

    return (
        <>
            <Button onClick={openModal}>
                {translate('onboarder.supplier.create_supplier.create_button.label')}
            </Button>
            {isOpen && (<Modal
                onClose={closeModal}
                closeTitle={translate('pim_common.close')}
            >
                <Modal.TopLeftButtons>
                    <Button>Top left button</Button>
                </Modal.TopLeftButtons>
                <Modal.TopRightButtons>
                    <Button>Top right button</Button>
                </Modal.TopRightButtons>
            </Modal>)}
        </>
    );
};

export {CreateSupplier};
