import React, {ReactElement} from 'react';
import {MediaFileInput, FileInfo, Field, Helper, HelperProps} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';

const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png'];
const FILESIZE_LIMIT = 2000 * 1000;

const getBase64 = async (file: File): Promise<string> =>
  new Promise(resolve => {
    const reader = new FileReader();
    reader.readAsDataURL(file);
    reader.onload = (event: ProgressEvent<FileReader>) => {
      const target = event.target;
      if (null !== target) {
        resolve(target.result as string);
      }
    };
  });

type ImageUploaderProps = {
  label: string;
  image: string | null;
  validationErrors: string[];
  onChange: (image: string | null) => void;
  children: ReactElement<HelperProps>[] | ReactElement<HelperProps>;
};

const getFileExtension = (file: File) => {
  return (
    file.name
      .toLowerCase()
      .split('.')
      .pop() || ''
  );
};

const ImageUploader = ({label, image, validationErrors, onChange, children}: ImageUploaderProps) => {
  const translate = useTranslate();

  const handleUpload = async (file: File, onProgress: (progress: number) => void) => {
    if (file.size && FILESIZE_LIMIT < file.size) {
      throw new Error(translate('shared_catalog.branding.validation.filesize_too_large'));
    }

    if (!ALLOWED_EXTENSIONS.includes(getFileExtension(file))) {
      throw new Error(translate('shared_catalog.branding.validation.invalid_extension'));
    }

    const fileInfo = {
      filePath: await getBase64(file),
      originalFilename: file.name,
      size: file.size,
    };

    onProgress(100);

    return fileInfo;
  };

  const handleChange = async (fileInfo: FileInfo) => {
    onChange(fileInfo?.filePath ?? null);
  };

  return (
    <Field label={label}>
      <MediaFileInput
        clearTitle={translate('pim_common.remove')}
        onChange={handleChange}
        thumbnailUrl={image}
        uploadErrorLabel={translate('shared_catalog.branding.validation.invalid_file')}
        uploader={handleUpload}
        uploadingLabel={translate('shared_catalog.branding.uploading')}
        value={image ? {filePath: image, originalFilename: ''} : null}
        placeholder={translate('shared_catalog.branding.upload_placeholder')}
      />
      {children}
      {validationErrors.map(error => (
        <Helper key={error} inline={true} level="error">
          {error}
        </Helper>
      ))}
    </Field>
  );
};

export {ImageUploader};
