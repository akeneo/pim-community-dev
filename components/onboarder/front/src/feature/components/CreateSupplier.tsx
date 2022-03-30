import React, {useState} from 'react';
import {Button, Field, Modal, NoResultsIllustration, TextInput, useBooleanState} from 'akeneo-design-system';
import {NotificationLevel, useNotify, useRoute, useTranslate} from '@akeneo-pim-community/shared';
import styled from 'styled-components';

type CreateSupplierProps = {
    onSupplierCreated: () => void;
    createButtonlabel: string;
};

const StyledField = styled(Field)`
    margin-bottom: 20px;
`;

const LABEL_AND_CODE_MAX_LENGTH = 200;

const CreateSupplier = ({onSupplierCreated, createButtonlabel}: CreateSupplierProps) => {
    const [isOpen, openModal, closeModal] = useBooleanState(false);
    const translate = useTranslate();
    const saveRoute = useRoute('onboarder_serenity_supplier_create');
    const notify = useNotify();
    const [code, setCode] = useState('');
    const [label, setLabel] = useState('');
    const [codeHasBeenChangedManually, setCodeHasBeenChangedManually] = useState(false);

    const manuallyUpdateCode = (code: string) => {
        setCodeHasBeenChangedManually(true);
        setCode(cleanCode(code));
    };

    const onChangeLabel = (label: string) => {
        setLabel(label);
        if (!codeHasBeenChangedManually) {
            setCode(cleanCode(label));
        }
    };

    const onCloseModal = () => {
        closeModal();
        setCode('');
        setLabel('');
        setCodeHasBeenChangedManually(false);
    };

    const isSaveDisabled = () => {
        return '' === code.trim() || '' === label.trim();
    };

    const saveSupplier = async () => {
        const response = await fetch(saveRoute, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: `code=${encodeURIComponent(code)}&label=${encodeURIComponent(label)}`,
        });

        if (!response.ok) {
            if (response.status === 409) {
                notify(
                    NotificationLevel.ERROR,
                    translate('onboarder.supplier.supplier_create.error.supplier_already_exists', {supplierCode: code})
                );
                return;
            }

            notify(NotificationLevel.ERROR, translate('onboarder.supplier.supplier_create.error.unknown_error'));

            return;
        }

        onSupplierCreated();
        notify(
            NotificationLevel.SUCCESS,
            translate('onboarder.supplier.supplier_create.notification.title'),
            translate('onboarder.supplier.supplier_create.notification.content')
        );
        onCloseModal();
    };

    const cleanCode = (code: string): string => {
        return code
            .trim()
            .toLowerCase()
            .replace(/[^a-z0-9]/g, '_');
    };

    return (
        <>
            <Button onClick={openModal}>{createButtonlabel}</Button>
            {isOpen && (
                <Modal
                    onClose={onCloseModal}
                    closeTitle={translate('pim_common.close')}
                    illustration={<NoResultsIllustration />}
                >
                    <Modal.SectionTitle color="brand">
                        {translate('onboarder.supplier.supplier_create.modal.title')}
                    </Modal.SectionTitle>
                    <Modal.Title>{translate('pim_common.create')}</Modal.Title>
                    <StyledField label={translate('onboarder.supplier.supplier_create.modal.label.label')}>
                        <TextInput
                            onChange={onChangeLabel}
                            maxLength={LABEL_AND_CODE_MAX_LENGTH}
                            value={label}
                            placeholder={translate('onboarder.supplier.supplier_create.modal.label.label')}
                        />
                    </StyledField>
                    <StyledField label={translate('onboarder.supplier.supplier_create.modal.code.label')}>
                        <TextInput
                            onChange={manuallyUpdateCode}
                            value={code}
                            maxLength={LABEL_AND_CODE_MAX_LENGTH}
                            placeholder={translate('onboarder.supplier.supplier_create.modal.code.label')}
                        />
                    </StyledField>
                    <Modal.BottomButtons>
                        <Button level="tertiary" onClick={onCloseModal}>
                            {translate('pim_common.cancel')}
                        </Button>
                        <Button level="primary" onClick={saveSupplier} disabled={isSaveDisabled()}>
                            {translate('pim_common.save')}
                        </Button>
                    </Modal.BottomButtons>
                </Modal>
            )}
        </>
    );
};

export {CreateSupplier};
