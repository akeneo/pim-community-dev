import React from 'react';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';

interface deleteConfirmationModalProps {
    attributeOptionCode: string;
    confirmDelete: () => void;
    cancelDelete: () => void;
}

const DeleteConfirmationModal = ({attributeOptionCode, confirmDelete, cancelDelete}: deleteConfirmationModalProps) => {
    const translate = useTranslate();

    return (
        <div className="modal modal--fullPage in" role="attribute-option-delete-confirmation-modal">
            <div className="AknFullPage">
                <div className="AknFullPage-content AknFullPage-content--withIllustration">
                    <div>
                        <div className="AknFullPage-image AknFullPage-illustration AknFullPage-illustration--delete"/>
                    </div>
                    <div>
                        <div className="AknFullPage-titleContainer">
                            <div className="AknFullPage-title">
                                {translate('pim_enrich.entity.fallback.module.delete.title', {'itemName': attributeOptionCode})}
                            </div>
                            <div className="AknFullPage-description">
                                {translate('pim_enrich.entity.fallback.module.delete.item_placeholder', {'itemName': attributeOptionCode})}
                            </div>
                        </div>
                        <div className="modal-body"/>
                        <div className="AknButtonList">
                            <div className="AknButton AknButton--grey AknButtonList-item cancel" onClick={cancelDelete} role="attribute-option-confirm-cancel-button">
                                {translate('pim_common.cancel')}
                            </div>
                            <div title="Delete" className="AknButton AknButtonList-item AknButton--apply AknButton--important ok" onClick={confirmDelete} role="attribute-option-confirm-delete-button">
                                {translate('pim_common.delete')}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div className="AknFullPage-cancel cancel" onClick={cancelDelete} role="attribute-option-confirm-cancel-button"/>
        </div>
    );
};

export default DeleteConfirmationModal;
