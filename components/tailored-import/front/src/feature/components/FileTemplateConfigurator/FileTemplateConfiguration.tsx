import React, {Dispatch, SetStateAction, useEffect, useState} from 'react';
import styled from 'styled-components';
import {ValidationError} from '@akeneo-pim-community/shared';
import {FileTemplateConfigurator} from './FileTemplateConfigurator';
import {FileTemplatePreview} from './FileTemplatePreview';
import {FileStructure, FileTemplateInformation, getDefaultFileStructure} from '../../models';
import {useFileTemplateInformationFetcher} from '../../hooks';

const FileTemplateConfiguratorContainer = styled.div`
  display: flex;
  flex-direction: column;
  gap: 20px;
  overflow-y: auto;
`;

type FileTemplateConfigurationProps = {
  fileKey: string;
  fileStructure: FileStructure;
  onFileStructureChange: Dispatch<SetStateAction<FileStructure>>;
  validationErrors: ValidationError[];
};

const FileTemplateConfiguration = ({
  fileKey,
  fileStructure,
  onFileStructureChange,
  validationErrors,
}: FileTemplateConfigurationProps) => {
  const [fileTemplateInformation, setFileTemplateInformation] = useState<FileTemplateInformation | null>(null);
  const fileTemplateInformationFetcher = useFileTemplateInformationFetcher();

  useEffect(() => {
    const refreshFileTemplateInformation = async () => {
      const fileTemplateInformation = await fileTemplateInformationFetcher(fileKey, null);
      setFileTemplateInformation(fileTemplateInformation);
      onFileStructureChange(fileStructure => ({...fileStructure, sheet_name: fileTemplateInformation.sheet_names[0]}));
    };

    if (null === fileStructure.sheet_name || fileTemplateInformation === null) {
      void refreshFileTemplateInformation();
    }
  }, [
    fileTemplateInformation,
    fileTemplateInformationFetcher,
    fileKey,
    onFileStructureChange,
    fileStructure.sheet_name,
  ]);

  const handleSheetChange = async (sheetName: string) => {
    if (fileTemplateInformation) {
      onFileStructureChange({...getDefaultFileStructure(), sheet_name: sheetName});
      setFileTemplateInformation(await fileTemplateInformationFetcher(fileKey, sheetName));
    }
  };

  if (!fileTemplateInformation) {
    return null;
  }

  return (
    <FileTemplateConfiguratorContainer>
      <FileTemplateConfigurator
        fileTemplateInformation={fileTemplateInformation}
        fileStructure={fileStructure}
        onFileStructureChange={onFileStructureChange}
        onSheetChange={handleSheetChange}
        validationErrors={validationErrors}
      />
      <FileTemplatePreview fileTemplateInformation={fileTemplateInformation} fileStructure={fileStructure} />
    </FileTemplateConfiguratorContainer>
  );
};

export {FileTemplateConfiguration};
