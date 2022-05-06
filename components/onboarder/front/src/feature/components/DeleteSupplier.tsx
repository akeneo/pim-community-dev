import React from 'react';
import {Button, DeleteIllustration, getFontSize, Helper, Modal} from 'akeneo-design-system';
import {NotificationLevel, useNotify, useRoute, useTranslate} from '@akeneo-pim-community/shared';
import styled from 'styled-components';

type Props = {
    identifier: string;
    onSupplierDeleted: () => void;
    onCloseModal: () => void;
};

const DeleteSupplier = ({identifier, onSupplierDeleted, onCloseModal}: Props) => {
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
        onSupplierDeleted();
    };

    return (
        <Modal onClose={onCloseModal} closeTitle={translate('pim_common.close')} illustration={<DeleteIllustration />}>
            <Modal.SectionTitle color="brand">
                {translate('onboarder.supplier.supplier_delete.modal.title')}
            </Modal.SectionTitle>
            <Modal.Title>{translate('pim_common.delete')}</Modal.Title>
            <ConfirmationText>
                {translate('onboarder.supplier.supplier_delete.modal.confirmation_question')}
            </ConfirmationText>
            <StyledHelper level="warning">{translate('onboarder.supplier.supplier_delete.modal.warning')}</StyledHelper>
            <Modal.BottomButtons>
                <Button level="tertiary" onClick={onCloseModal}>
                    {translate('pim_common.cancel')}
                </Button>
                <Button level="danger" onClick={deleteSupplier} data-testid={'delete-button'}>
                    {translate('pim_common.delete')}
                </Button>
            </Modal.BottomButtons>
        </Modal>
    );
};

const ConfirmationText = styled.div`
    font-size: ${getFontSize('bigger')};
`;

const StyledHelper = styled(Helper)`
    margin: 10px 0 10px 0;
`;

export {DeleteSupplier};
