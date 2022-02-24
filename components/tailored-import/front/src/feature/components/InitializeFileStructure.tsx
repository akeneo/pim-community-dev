import React, {useState} from 'react';
import styled from 'styled-components';
import {
  Button,
  MediaFileInput,
  Modal,
  FileInfo,
  useBooleanState,
  Placeholder,
  AttributesIllustration,
  Helper,
} from 'akeneo-design-system';
import Products from 'akeneo-design-system/static/illustrations/Products.svg';
import {useTranslate, useUploader, ValidationError, formatParameters} from '@akeneo-pim-community/shared';
import {useReadColumns} from '../hooks';
import {Column, FileStructure, getDefaultFileStructure} from '../models';

const Container = styled.div`
  width: 100%;
  max-height: 100vh;
  padding-top: 40px;
  height: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
`;

const Content = styled.div`
  flex-grow: 1;
  overflow-y: auto;
  width: 100%;
  display: flex;
  flex-direction: column;
  gap: 10px;
  padding: 0 2px;
`;

const PlaceholderContainer = styled.div`
  display: flex;
  flex-direction: column;
  height: 100%;
  gap: 20px;
  align-items: center;
  padding: 40px;
`;

type InitializeFileStructureProps = {
  onConfirm: (fileKey: string, columns: Column[]) => void;
};

const InitializeFileStructure = ({onConfirm}: InitializeFileStructureProps) => {
  const translate = useTranslate();
  const [isModalOpen, openModal, closeModal] = useBooleanState();
  const [file, setFile] = useState<FileInfo | null>(null);
  const [validationErrors, setValidationErrors] = useState<ValidationError[]>([]);
  const [fileStructure] = useState<FileStructure>(getDefaultFileStructure());
  const [uploader] = useUploader('pimee_tailored_import_upload_structure_file_action');

  const uploaderWithValidation = async (file: File, onProgress: (ratio: number) => void): Promise<FileInfo> => {
    setValidationErrors([]);

    try {
      return await uploader(file, onProgress);
    } catch (response: any) {
      const validationErrors = JSON.parse(response);
      setValidationErrors(formatParameters(validationErrors));

      return Promise.reject(response);
    }
  };

  const readColumns = useReadColumns();

  const handleConfirm = async () => {
    setValidationErrors([]);

    if (null !== file) {
      try {
        const columns = await readColumns(file.filePath, fileStructure);

        onConfirm(file.filePath, columns);
        closeModal();
      } catch (validationErrors: any) {
        setValidationErrors(formatParameters(validationErrors));
      }
    }
  };

  const handleClose = () => {
    setFile(null);
    closeModal();
  };

  return isModalOpen ? (
    <Modal onClose={handleClose} closeTitle={translate('pim_common.close')}>
      <Modal.TopRightButtons>
        <Button disabled={null === file} onClick={handleConfirm}>
          {translate('pim_common.confirm')}
        </Button>
      </Modal.TopRightButtons>
      <Container>
        <Modal.SectionTitle color="brand">
          {translate('akeneo.tailored_import.file_structure.modal.subtitle')}
        </Modal.SectionTitle>
        <Modal.Title>{translate('akeneo.tailored_import.file_structure.modal.title')}</Modal.Title>
        <Content>
          <Helper>{translate('akeneo.tailored_import.file_structure.modal.helper')}</Helper>
          <MediaFileInput
            value={file}
            onChange={setFile}
            thumbnailUrl={Products}
            uploader={uploaderWithValidation}
            placeholder={translate('akeneo.tailored_import.file_structure.modal.upload.placeholder')}
            uploadingLabel={translate('akeneo.tailored_import.file_structure.modal.upload.uploading')}
            clearTitle={translate('pim_common.clear_value')}
            uploadErrorLabel={translate('akeneo.tailored_import.file_structure.modal.upload.error')}
            invalid={0 < validationErrors.length}
          />
          {validationErrors.map((error, index) => (
            <Helper key={index} inline={true} level="error">
              {translate(error.messageTemplate, error.parameters)}
            </Helper>
          ))}
        </Content>
      </Container>
    </Modal>
  ) : (
    <PlaceholderContainer>
      <Placeholder
        title={translate('akeneo.tailored_import.file_structure.placeholder.title')}
        illustration={<AttributesIllustration />}
      />
      <Button level="primary" onClick={openModal}>
        {translate('akeneo.tailored_import.file_structure.placeholder.button')}
      </Button>
    </PlaceholderContainer>
  );
};

export {InitializeFileStructure};
