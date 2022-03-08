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
import {FileTemplateConfigurator} from './FileTemplateConfigurator/FileTemplateConfigurator';
import {FileTemplateInformation} from '../models/FileTemplateInformation';
import {useFileTemplateInformationFetcher} from '../hooks/useFileTemplateInformationFetcher';

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
  onConfirm: (fileKey: string, columns: Column[], identifierColumn: Column | null, fileStructure: FileStructure) => void;
};

const InitializeFileStructure = ({onConfirm}: InitializeFileStructureProps) => {
  const translate = useTranslate();
  const [isModalOpen, openModal, closeModal] = useBooleanState();
  const [fileTemplateInformation, setFileTemplateInformation] = useState<FileTemplateInformation | null>(null);
  const [validationErrors, setValidationErrors] = useState<ValidationError[]>([]);
  const [fileStructure, setFileStructure] = useState<FileStructure>(getDefaultFileStructure());
  const [uploader] = useUploader('pimee_tailored_import_upload_structure_file_action');
  const readColumns = useReadColumns();
  const fileTemplateInformationFetcher = useFileTemplateInformationFetcher();

  const uploadFileTemplate = async (file: File, onProgress: (ratio: number) => void): Promise<FileInfo> => {
    setValidationErrors([]);
    try {
      return await uploader(file, onProgress);
    } catch (response: any) {
      const validationErrors = JSON.parse(response);
      setValidationErrors(formatParameters(validationErrors));

      return Promise.reject(response);
    }
  };

  const handleConfirm = async () => {
    setValidationErrors([]);

    if (null !== fileTemplateInformation) {
      try {
        const columns = await readColumns(fileTemplateInformation.file_info.filePath, fileStructure);

        const columnIdentifier = columns.find(column => column.index === fileStructure.column_identifier_position) ?? null;
        onConfirm(fileTemplateInformation.file_info.filePath, columns, columnIdentifier, fileStructure);
        closeModal();
      } catch (validationErrors: any) {
        setValidationErrors(formatParameters(validationErrors));
      }
    }
  };

  const handleFileUpload = async (file: FileInfo | null) => {
    const fileTemplateInformation = file ? await fileTemplateInformationFetcher(file, null) : null;

    setFileStructure(fileStructure => ({...fileStructure, sheet_name: fileTemplateInformation?.sheets[0] ?? null}));
    setFileTemplateInformation(fileTemplateInformation);
  };

  const handleSheetChange = async (sheetName: string) => {
    if (!fileTemplateInformation) return;

    const newFileStructure = {...getDefaultFileStructure(), sheet_name: sheetName};
    setFileStructure(newFileStructure);
    setFileTemplateInformation(
      await fileTemplateInformationFetcher(fileTemplateInformation.file_info, newFileStructure)
    );
  };

  const handleHeaderPositionChange = async (headerPosition: number) => {
    if (!fileTemplateInformation) return;

    const newFileStructure = {...fileStructure, header_line: headerPosition};
    setFileStructure(newFileStructure);
    setFileTemplateInformation(
      await fileTemplateInformationFetcher(fileTemplateInformation.file_info, newFileStructure)
    );
  };

  const handleClose = () => {
    setFileTemplateInformation(null);
    closeModal();
  };

  return isModalOpen ? (
    <Modal onClose={handleClose} closeTitle={translate('pim_common.close')}>
      <Modal.TopRightButtons>
        <Button disabled={null === fileTemplateInformation} onClick={handleConfirm}>
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
          {!fileTemplateInformation || validationErrors.length > 0 ? (
            <>
              <MediaFileInput
                value={fileTemplateInformation?.file_info ?? null}
                onChange={handleFileUpload}
                thumbnailUrl={Products}
                uploader={uploadFileTemplate}
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
            </>
          ) : (
            <FileTemplateConfigurator
              fileStructure={fileStructure}
              fileTemplateInformation={fileTemplateInformation}
              onFileStructureChange={setFileStructure}
              onSheetChange={handleSheetChange}
              onHeaderPositionChange={handleHeaderPositionChange}
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
      <Button ghost={true} level="secondary" onClick={openModal}>
        {translate('akeneo.tailored_import.file_structure.placeholder.button')}
      </Button>
    </Placeholder>
  );
};

export {InitializeFileStructure};
