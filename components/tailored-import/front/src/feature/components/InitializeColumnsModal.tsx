import React, {useState} from 'react';
import {Button, Field, Modal, TextAreaInput, ImportIllustration} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {Column, generateColumns} from '../models';

type InitializeColumnsModalProps = {
  onConfirm: (columns: Column[]) => void;
  onCancel: () => void;
};

const InitializeColumnsModal = ({onConfirm, onCancel}: InitializeColumnsModalProps) => {
  const translate = useTranslate();
  const [sheetContent, setSheetContent] = useState<string>('');

  const handleConfirm = (): void => {
    const generatedColumns = generateColumns(sheetContent);
    onConfirm(generatedColumns);
  };

  return (
    <Modal illustration={<ImportIllustration />} onClose={onCancel} closeTitle={translate('pim_common.close')}>
      <Modal.SectionTitle color="brand">
        {translate('akeneo.tailored_import.column_initialization.modal.subtitle')}
      </Modal.SectionTitle>
      <Modal.Title>{translate('akeneo.tailored_import.column_initialization.modal.title')}</Modal.Title>
      <Field label={translate('akeneo.tailored_import.column_initialization.modal.columns.label')}>
        <TextAreaInput
          onChange={newSheetContent => setSheetContent(newSheetContent)}
          placeholder={translate('akeneo.tailored_import.column_initialization.modal.columns.placeholder')}
          value={sheetContent}
        />
      </Field>
      <Modal.BottomButtons>
        <Button onClick={onCancel} level="tertiary">
          {translate('pim_common.cancel')}
        </Button>
        <Button onClick={handleConfirm}>{translate('pim_common.confirm')}</Button>
      </Modal.BottomButtons>
    </Modal>
  );
};

export type {InitializeColumnsModalProps};
export {InitializeColumnsModal};
