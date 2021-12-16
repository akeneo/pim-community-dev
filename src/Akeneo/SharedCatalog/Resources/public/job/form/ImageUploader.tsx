import React, {useState, useEffect} from 'react';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {MediaFileInput, FileInfo, Field} from 'akeneo-design-system';

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
  const [errors, setErrors] = useState<string[]>([]);
  const translate = useTranslate();

  const handleUpload = async (file: File, onProgress: (progress: number) => void) => {
    const fileInfo = {
      filePath: await getBase64(file),
      originalFilename: file.name,
      size: file.size,
    };
    onProgress(100);
    return fileInfo;
  };

  const handleChange = async (fileInfo: FileInfo) => {
    setErrors([]);

    if (null === fileInfo) {
      setCurrentImage(null);
      return;
    }

    const {filePath, originalFilename, size} = fileInfo;

    if (size && FILESIZE_LIMIT < size) {
      setErrors(errors => [...errors, translate('shared_catalog.branding.filesize_too_large')]);
      return;
    }

    if (
      !ALLOWED_EXTENSIONS.includes(
        originalFilename
          .toLowerCase()
          .split('.')
          .pop() || ''
      )
    ) {
      setErrors(errors => [
        ...errors,
        translate('shared_catalog.branding.invalid_extension', {allowed_extensions: ALLOWED_EXTENSIONS.join(', ')}),
      ]);
      return;
    }

    setCurrentImage(filePath);
  };

  useEffect(() => {
    onChange(currentImage);
    setErrors([]);
  }, [currentImage]);

  useEffect(() => {
    setErrors(validationErrors);
  }, []);

  return (
    <Field label={label}>
      <MediaFileInput
        clearTitle={translate('pim_common.remove')}
        onChange={handleChange}
        thumbnailUrl={currentImage}
        uploadErrorLabel={errors[0]}
        uploader={handleUpload}
        uploadingLabel={translate('shared_catalog.branding.uploading')}
        value={currentImage ? {filePath: currentImage, originalFilename: ''} : null}
      />
    </Field>
  );
};

export {ImageUploader};
