import {DeleteIllustration, Button, Field, Modal, TextInput, Helper} from 'akeneo-design-system';
import React from 'react';
import {useTranslate} from '@akeneo-pim-community/shared';
import styled from 'styled-components';

const FieldsList = styled.div`
  gap: 20px;
  display: flex;
  flex-direction: column;
`;

type DeleteColumnModalProps = {
    close: () => void;
    onDelete: () => void;
    columnCode: string;
};

const DeleteColumnModal: React.FC<DeleteColumnModalProps> = ({close, onDelete, columnCode}) => {
    const translate = useTranslate();
    const [typedColumnCode, setTypedColumnCode] =  React.useState<string>("");

    const handleCancel = ()=>{
        // In construction
        close();
    }

    const handleDelete = ()=>{
        // In construction
        close();
        onDelete();
    }


    return (
        <Modal closeTitle={translate('pim_common.close')} onClose={close} illustration={<DeleteIllustration />}>
            <Modal.SectionTitle color='brand'>
                {translate('pim_table_attribute.form.attribute.table_attribute')}
            </Modal.SectionTitle>
            <Modal.Title>{/*translate('pim_table_attribute.form.attribute.delete_column')*/} !Confirm deletion</Modal.Title>
            <FieldsList>
                <div>
                    Are you sure you want to delete this column
                </div>
                <Helper
                    level="info"
                >
                    Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.
                </Helper>
                <Field label={`TODO:Please Type ${columnCode}`}>
                    <TextInput onChange={setTypedColumnCode}  value={typedColumnCode} />
                </Field>
            </FieldsList>
            <Modal.BottomButtons>
                <Button level='tertiary' onClick={handleCancel}>
                    {/*translate('pim_common.create')*/}
                    {/*translate('pim_common.delete')*/}
                    Cancel
                </Button>
                <Button level='danger' onClick={handleDelete} disabled={ typedColumnCode !== columnCode }>
                    {/*translate('pim_common.create')*/}
                    {/*translate('pim_common.delete')*/}
                    Delete
                </Button>
            </Modal.BottomButtons>
        </Modal>
    );
};

export {DeleteColumnModal};
