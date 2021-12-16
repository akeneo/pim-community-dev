import React, {useState, useEffect} from 'react';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {MediaFileInput, FileInfo, Field, Helper} from 'akeneo-design-system';

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
};

const ImageUploader = ({label, image, validationErrors, onChange}: ImageUploaderProps) => {
  const [currentImage, setCurrentImage] = useState<string | null>(image);
  const translate = useTranslate();

  const handleUpload = async (file: File, onProgress: (progress: number) => void) => {
    if (file.size && FILESIZE_LIMIT < file.size) {
      throw new Error(translate('shared_catalog.branding.filesize_too_large'));
    }

    if (
      !ALLOWED_EXTENSIONS.includes(
        file.name
          .toLowerCase()
          .split('.')
          .pop() || ''
      )
    ) {
      throw new Error(translate('shared_catalog.branding.invalid_extension'));
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
    setCurrentImage(fileInfo?.filePath);
  };

  useEffect(() => {
    onChange(currentImage);
  }, [currentImage]);

  return (
    <Field label={label}>
      <MediaFileInput
        clearTitle={translate('pim_common.remove')}
        onChange={handleChange}
        thumbnailUrl={currentImage}
        uploadErrorLabel={translate('shared_catalog.branding.invalid_file')}
        uploader={handleUpload}
        uploadingLabel={translate('shared_catalog.branding.uploading')}
        value={currentImage ? {filePath: currentImage, originalFilename: ''} : null}
      />
      {validationErrors.map(error => (
        <Helper key={error} inline={true} level="error">
          {error}
        </Helper>
      ))}
    </Field>
  );
};

export {ImageUploader};
