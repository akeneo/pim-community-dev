import React, {useState} from 'react';
import {Button, Modal, useBooleanState, NoResultsIllustration, Field, TextInput} from 'akeneo-design-system';
import {useTranslate, useRoute} from '@akeneo-pim-community/shared';
import styled from 'styled-components';

type CreateSupplierProps = {
    onSupplierCreated: () => void;
};

const StyledField = styled(Field)`
    margin-bottom: 20px;
`;

// Max length for supplier label and code
const LABEL_MAX_LENGTH = 200;

const CreateSupplier = ({onSupplierCreated}: CreateSupplierProps) => {
    const [isOpen, openModal, closeModal] = useBooleanState(false);
    const translate = useTranslate();
    // const saveRoute = useRoute('');
    const [code, setCode] = useState('');
    const [label, setLabel] = useState('');
    const [codeHasBeenChangedManually, setCodeHasBeenChangedManually] = useState(false);

    const manuallyUpdateCode = (code: string) => {
        setCodeHasBeenChangedManually(true);
        setCode(cleanCode(code));
    }

    const onChangeLabel = (label: string) => {
        setLabel(label);
        if (!codeHasBeenChangedManually) {
            setCode(cleanCode(label));
        }
    };

    const saveSupplier = async () => {
        // const response = await fetch(saveRoute, {
        //     method: 'POST',
        //     headers: {
        //         'Content-Type': 'application/json',
        //         'X-Requested-With': 'XMLHttpRequest',
        //     },
        //     body: JSON.stringify({code, label}),
        // });
        onSupplierCreated();
        closeModal();
    }

    const cleanCode = (code: string): string => {
        return code.trim().toLowerCase().replace(/[^a-z0-9]/g, '_');
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
                        <TextInput onChange={manuallyUpdateCode} value={code} maxLength={LABEL_MAX_LENGTH} />
                    </StyledField>
                    <StyledField label={translate('onboarder.supplier.create_supplier.label.label')}>
                        <TextInput onChange={onChangeLabel} maxLength={LABEL_MAX_LENGTH} value={label} />
                    </StyledField>
                    <Modal.BottomButtons>
                        <Button level="tertiary" onClick={closeModal}>
                            {translate('pim_common.cancel')}
                        </Button>
                        <Button level="primary" onClick={saveSupplier}>
                            {translate('pim_common.save')}
                        </Button>
                    </Modal.BottomButtons>
                </Modal>
            )}
        </>
    );
};

export {CreateSupplier};
