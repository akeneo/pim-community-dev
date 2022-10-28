import React, {FC, useState} from 'react';
import {Button, DeleteIllustration, Field, Modal, TextInput} from 'akeneo-design-system';
import {NotificationLevel, useNotify, useRouter, useTranslate} from '@akeneo-pim-community/shared';
import { StyledDelete } from './styles';

type DeleteGeneratorModalProps = {
    generatorCode: string;
    closeModal: () => void;
    deleteGenerator: () => void;
};

const DeleteIdentifierGeneratorModal: FC<DeleteGeneratorModalProps> =
    ({generatorCode, closeModal, deleteGenerator}) => {
    const translate = useTranslate();
    const router = useRouter();
    const notify = useNotify();
    const [isLoading] = useState<boolean>(false);
    const [attributeCodeConfirm, setAttributeCodeConfirm] = useState<string>('');

    const callDeleteGenerator = async (code: string): Promise<string> => {
        // todo search
        return fetch(router.generate('akeneo_identifier_generator_rest_delete', {code}), {
            method: 'DELETE',
            headers: [['X-Requested-With', 'XMLHttpRequest']],
        }).then(res => {
            if (!res.ok) throw new Error(res.statusText);
            return res.json();
        });
    };

    const confirmDelete = async () => {
        try {
            await callDeleteGenerator(generatorCode);
            notify(NotificationLevel.SUCCESS, translate('pim_identifier_generator.flash.delete.success', {code : generatorCode}));
            deleteGenerator();
        } catch (error) {
            notify(NotificationLevel.ERROR, translate('pim_identifier_generator.flash.delete.error'));
        }
    };

    return (
        <Modal closeTitle="Close" illustration={<DeleteIllustration />} onClose={closeModal}>
            <Modal.SectionTitle color="brand">{translate('pim_identifier_generator.deletion.operations')}</Modal.SectionTitle>
            <Modal.Title>{translate('pim_common.confirm_deletion')}</Modal.Title>

            <StyledDelete.Message>{translate('pim_identifier_generator.deletion.confirmation')}</StyledDelete.Message>
            <Field label={translate('pim_identifier_generator.deletion.type', {code : generatorCode})}>
                <TextInput
                    readOnly={isLoading}
                    value={attributeCodeConfirm}
                    onChange={setAttributeCodeConfirm}
                />
            </Field>
            <Modal.BottomButtons>
                <Button onClick={closeModal} level="tertiary">
                    {translate('pim_common.cancel')}
                </Button>
                <Button level="danger" className="ok" disabled={attributeCodeConfirm !== generatorCode} onClick={confirmDelete}>
                    {translate('pim_common.delete')}
                </Button>
            </Modal.BottomButtons>
        </Modal>
    );
};


// TODO to /styles

export {DeleteIdentifierGeneratorModal};
