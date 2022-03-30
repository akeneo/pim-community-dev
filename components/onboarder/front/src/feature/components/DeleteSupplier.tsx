import React from 'react';
import {
    Button,
    DeleteIcon,
    DeleteIllustration,
    getFontSize,
    Helper,
    Modal,
    pimTheme,
    useBooleanState,
} from 'akeneo-design-system';
import {NotificationLevel, useNotify, useTranslate, useRoute} from '@akeneo-pim-community/shared';
import styled from 'styled-components';

type Props = {
    identifier: string;
    onSupplierDeleted: () => void;
};

const DeleteSupplier = ({identifier, onSupplierDeleted}: Props) => {
    const [isOpen, openModal, closeModal] = useBooleanState(false);
    const translate = useTranslate();
    const notify = useNotify();
    const deleteRoute = useRoute('onboarder_serenity_supplier_delete', {identifier});

    const deleteSupplier = async () => {
        const response = await fetch(deleteRoute, {method: 'DELETE'});
        if (!response.ok) {
            notify(NotificationLevel.ERROR, translate('onboarder.supplier.supplier_delete.modal.unknown_error'));
            return;
        }

        notify(NotificationLevel.SUCCESS, translate('onboarder.supplier.supplier_delete.sucess_message'));
        closeModal();
        onSupplierDeleted();
    };

    return (
        <>
            <StyledDeleteIcon
                color={pimTheme.color.grey100}
                onClick={(event: any) => {
                    event.stopPropagation();
                    openModal();
                }}
                title={translate('pim_common.delete')}
            />
            {isOpen && (
                <Modal
                    onClose={closeModal}
                    closeTitle={translate('pim_common.close')}
                    illustration={<DeleteIllustration />}
                >
                    <Modal.SectionTitle color="brand">
                        {translate('onboarder.supplier.supplier_delete.modal.title')}
                    </Modal.SectionTitle>
                    <Modal.Title>{translate('pim_common.delete')}</Modal.Title>
                    <ConfirmationText>
                        {translate('onboarder.supplier.supplier_delete.modal.confirmation_question')}
                    </ConfirmationText>
                    <StyledHelper level="warning">
                        {translate('onboarder.supplier.supplier_delete.modal.warning')}
                    </StyledHelper>
                    <Modal.BottomButtons>
                        <Button level="tertiary" onClick={closeModal}>
                            {translate('pim_common.cancel')}
                        </Button>
                        <Button level="danger" onClick={deleteSupplier} data-testid={'delete-button'}>
                            {translate('pim_common.delete')}
                        </Button>
                    </Modal.BottomButtons>
                </Modal>
            )}
        </>
    );
};

const StyledDeleteIcon = styled(DeleteIcon)`
    cursor: pointer;
`;

const ConfirmationText = styled.div`
    font-size: ${getFontSize('bigger')};
`;

const StyledHelper = styled(Helper)`
    margin: 10px 0 10px 0;
`;

export {DeleteSupplier};
