import React, {useState} from 'react';
import styled from 'styled-components';
import {
  Button,
  Modal,
  FileInfo,
  useBooleanState,
  Placeholder,
  AttributesIllustration,
  Helper,
} from 'akeneo-design-system';
import {filterErrors, useTranslate, ValidationError, formatParameters} from '@akeneo-pim-community/shared';
import {useReadColumns} from '../hooks';
import {Column, FileStructure, getDefaultFileStructure} from '../models';
import {FileTemplateConfiguration} from '../components';
import {FileTemplateUploader} from './FileTemplateConfigurator';

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

type InitializeFileStructureProps = {
  onConfirm: (
    fileKey: string,
    columns: Column[],
    identifierColumn: Column | null,
    fileStructure: FileStructure
  ) => void;
};

const InitializeFileStructure = ({onConfirm}: InitializeFileStructureProps) => {
  const translate = useTranslate();
  const [isModalOpen, openModal, closeModal] = useBooleanState();
  const [fileInfo, setFileInfo] = useState<FileInfo | null>(null);
  const [validationErrors, setValidationErrors] = useState<ValidationError[]>([]);
  const [fileStructure, setFileStructure] = useState<FileStructure>(getDefaultFileStructure());
  const readColumns = useReadColumns();
  const fileStructureValidationErrors = filterErrors(validationErrors, '[file_structure]');

  const handleConfirm = async () => {
    setValidationErrors([]);

    if (null !== fileInfo) {
      try {
        const columns = await readColumns(fileInfo.filePath, fileStructure);

        const columnIdentifier =
          columns.find(column => column.index === fileStructure.unique_identifier_column) ?? null;
        onConfirm(fileInfo.filePath, columns, columnIdentifier, fileStructure);
        closeModal();
      } catch (validationErrors: any) {
        setValidationErrors(formatParameters(validationErrors));
      }
    }
  };

  const handleFileUpload = async (file: FileInfo | null) => {
    setFileInfo(file);
  };

  const handleClose = () => {
    setFileInfo(null);
    setFileStructure(getDefaultFileStructure());
    closeModal();
  };

  const handleOpenModal = () => {
    openModal();
  };

  const handlePrevious = () => {
    setFileStructure(getDefaultFileStructure());
    setFileInfo(null);
  };

  return isModalOpen ? (
    <Modal onClose={handleClose} closeTitle={translate('pim_common.close')}>
      {fileInfo && (
        <Modal.TopLeftButtons>
          <Button onClick={handlePrevious} level="tertiary">
            {translate('pim_common.previous')}
          </Button>
        </Modal.TopLeftButtons>
      )}
      <Modal.TopRightButtons>
        <Button disabled={null === fileInfo} onClick={handleConfirm}>
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
          {!fileInfo ? (
            <FileTemplateUploader onFileTemplateUpload={handleFileUpload} />
          ) : (
            <FileTemplateConfiguration
              fileInfo={fileInfo}
              fileStructure={fileStructure}
              onFileStructureChange={setFileStructure}
              validationErrors={fileStructureValidationErrors}
            />
          )}
        </Content>
      </Container>
    </Modal>
  ) : (
    <Placeholder
      size="large"
      title={translate('akeneo.tailored_import.file_structure.placeholder.title')}
      illustration={<AttributesIllustration />}
    >
      <Button ghost={true} level="secondary" onClick={handleOpenModal}>
        {translate('akeneo.tailored_import.file_structure.placeholder.button')}
      </Button>
    </Placeholder>
  );
};

export {InitializeFileStructure};
