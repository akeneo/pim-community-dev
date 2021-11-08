import React, {useState, useEffect, ChangeEvent} from 'react';
import styled from 'styled-components';
import {useTranslate} from '@akeneo-pim-community/shared';
import {CloseIcon, Helper} from 'akeneo-design-system';

const PLACEHOLDER_PATH = '/bundles/pimui/images/illustrations/Import.svg';
const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png'];
const FILESIZE_LIMIT = 2000 * 1000;

const Container = styled.div`
  width: 400px;
`;

const FileInputContainer = styled.div`
  position: relative;
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 20px;
  border: 1px solid ${({theme}) => theme.color.grey80};
  margin-top: 8px;
  border-radius: 4px;
`;

const FileInput = styled.input`
  position: absolute;
  opacity: 0;
  top: 0;
  width: 100%;
  height: 100%;
  cursor: pointer;
`;

const Image = styled.img`
  max-height: 120px;
  width: auto;
  margin: 8px 0;
`;

const ImagePlaceholder = styled.div`
  width: 120px;
  height: 120px;
  margin: 8px 0;
`;

const RemoveButton = styled(CloseIcon)`
  position: absolute;
  top: 10px;
  right: 10px;
  z-index: 10;
  cursor: pointer;
`;

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
  image: string | null;
  validationErrors: string[];
  onChange: (image: string | null) => void;
};

const ImageUploader = ({image, validationErrors, onChange}: ImageUploaderProps) => {
  const [isLoading, setLoading] = useState<boolean>(false);
  const [currentImage, setCurrentImage] = useState<string | null>(image);
  const [errors, setErrors] = useState<string[]>([]);
  const translate = useTranslate();

  const handleChange = async (event: ChangeEvent<HTMLInputElement>) => {
    if (null !== event.target.files && event.target.files[0]) {
      setErrors([]);

      const file = event.target.files[0];
      if (FILESIZE_LIMIT < file.size) {
        setErrors(errors => [...errors, translate('shared_catalog.branding.filesize_too_large')]);
        return;
      }

      if (
        !ALLOWED_EXTENSIONS.includes(
          file.name
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

      setLoading(true);
      setCurrentImage(await getBase64(file));
      setLoading(false);
    }
  };

  const handleRemove = () => setCurrentImage(null);

  useEffect(() => {
    onChange(currentImage);
    setErrors([]);
  }, [currentImage]);

  useEffect(() => {
    setErrors(validationErrors);
  }, []);

  return (
    <Container>
      <div>{translate('shared_catalog.branding.upload')}</div>
      <FileInputContainer>
        <FileInput role="file-input" type="file" accept={ALLOWED_EXTENSIONS.join(',')} onChange={handleChange} />
        {isLoading ? (
          <ImagePlaceholder className="AknLoadingPlaceHolder" />
        ) : (
          <Image src={currentImage || PLACEHOLDER_PATH} alt={translate('pim_common.branding')} />
        )}
        {translate('pim_common.media_upload')}
        {null !== currentImage && !isLoading && (
          <RemoveButton onClick={handleRemove} size={20} title={translate('pim_common.remove')} />
        )}
      </FileInputContainer>
      {errors.map(error => (
        <Helper key={error} inline={true} level="error">
          {error}
        </Helper>
      ))}
    </Container>
  );
};

export {ImageUploader};
