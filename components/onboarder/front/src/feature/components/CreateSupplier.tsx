import React, {useState} from 'react';
import {Button, Modal, useBooleanState, NoResultsIllustration, Field, TextInput} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import styled from 'styled-components';

type CreateSupplierProps = {
    onSupplierCreated: () => void;
};

const StyledField = styled(Field)`
    margin-bottom: 20px;
`;

// Max length for supplier label and code
const SUPPLIER_LABEL_MAX_LENGTH = 200;

const CreateSupplier = ({onSupplierCreated}: CreateSupplierProps) => {
    const [isOpen, openModal, closeModal] = useBooleanState(false);
    const translate = useTranslate();

    const [code, setSupplierCode] = useState('');
    let supplierCodeHasBeenChangedManually = false;

    const manuallyUpdateSupplierCode = (supplierCode: string) => {
        supplierCodeHasBeenChangedManually = true;
        setSupplierCode(cleanSupplierCode(supplierCode));
    }

    const updateSupplierCode = (supplierLabel: string) => {
        if (!supplierCodeHasBeenChangedManually) {
            setSupplierCode(cleanSupplierCode(supplierLabel));
        }
    };

    const cleanSupplierCode = (supplierCode: string): string => {
        return supplierCode.trim().toLowerCase().replace(/[^a-z0-9]/g, '_');
    }

    return (
        <>
            <Button onClick={openModal}>{translate('onboarder.supplier.create_supplier.create_button.label')}</Button>
            {isOpen && (
                <Modal
                    onClose={closeModal}
                    closeTitle={translate('pim_common.close')}
                    illustration={<NoResultsIllustration />}
                >
                    <Modal.SectionTitle color="brand">
                        {translate('onboarder.supplier.create_supplier.title')}
                    </Modal.SectionTitle>
                    <Modal.Title>{translate('pim_common.create')}</Modal.Title>
                    <StyledField label={translate('onboarder.supplier.create_supplier.code.label')}>
                        <TextInput onChange={manuallyUpdateSupplierCode} value={code} maxLength={SUPPLIER_LABEL_MAX_LENGTH} />
                    </StyledField>
                    <StyledField label={translate('onboarder.supplier.create_supplier.label.label')}>
                        <TextInput onChange={updateSupplierCode} maxLength={SUPPLIER_LABEL_MAX_LENGTH} />
                    </StyledField>
                    <Modal.BottomButtons>
                        <Button level="tertiary" onClick={closeModal}>
                            {translate('pim_common.cancel')}
                        </Button>
                        <Button level="primary" onClick={() => {}}>
                            {translate('pim_common.save')}
                        </Button>
                    </Modal.BottomButtons>
                </Modal>
            )}
        </>
    );
};

export {CreateSupplier};
