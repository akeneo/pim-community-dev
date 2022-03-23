import React from 'react';
import {FileInfo, Helper, MediaFileInput} from 'akeneo-design-system';
import Products from 'akeneo-design-system/static/illustrations/Products.svg';
import {formatParameters, useTranslate, useUploader, ValidationError} from '@akeneo-pim-community/shared';

type FileTemplateUploaderProps = {
  onFileTemplateUpload: (fileInfo: FileInfo | null) => void;
};

const FileTemplateUploader = ({onFileTemplateUpload}: FileTemplateUploaderProps) => {
  const translate = useTranslate();
  const [uploader] = useUploader('pimee_tailored_import_upload_structure_file_action');
  const [validationErrors, setValidationErrors] = React.useState<ValidationError[]>([]);

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

  return (
    <>
      <MediaFileInput
        value={null}
        onChange={onFileTemplateUpload}
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
  );
};

export {FileTemplateUploader};
