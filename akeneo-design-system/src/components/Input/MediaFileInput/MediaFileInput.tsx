import React, {Ref, useEffect, useRef, useState} from 'react';
import styled, {css} from 'styled-components';
import {Key, Override} from '../../../shared';
import {InputProps} from '../InputProps';
import {AkeneoThemedProps, getColor, getFontSize} from '../../../theme';
import {ImportIllustration} from '../../../illustrations';
import {IconButton, Image} from '../../../components';
import {ProgressBar} from '../../ProgressBar/ProgressBar';
import {CloseIcon, DownloadIcon, FullscreenIcon, LockIcon} from '../../../icons';
import {useBooleanState, useShortcut} from '../../../hooks';
import {FileInfo} from './FileInfo';
import {FullscreenPreview} from './FullscreenPreview';
import DefaultPictureIllustration from '../../../../static/illustrations/DefaultPicture.svg';

const ActionButton = styled(IconButton)`
  color: ${getColor('grey', 100)};
`;

const MediaFileInputContainer = styled.div<{isCompact: boolean; readOnly: boolean} & AkeneoThemedProps>`
  position: relative;
  display: flex;
  flex-direction: ${({isCompact}) => (isCompact ? 'row' : 'column')};
  align-items: center;
  padding: 12px;
  padding-top: ${({isCompact}) => (isCompact ? 12 : 20)}px;
  border: 1px solid ${({invalid}) => (invalid ? getColor('red', 100) : getColor('grey', 80))};
  border-radius: 2px;
  height: ${({isCompact}) => (isCompact ? 74 : 180)}px;
  gap: ${({isCompact}) => (isCompact ? 10 : 0)}px;
  outline-style: none;
  box-sizing: border-box;
  background: ${({readOnly}) => (readOnly ? getColor('grey', 20) : getColor('white'))};
  cursor: ${({readOnly}) => (readOnly ? 'not-allowed' : 'auto')};
  overflow: hidden;

  ${({readOnly}) =>
    !readOnly &&
    css`
      &:focus {
        box-shadow: 0 0 0 2px ${getColor('blue', 40)};
      }
      &:hover {
        ${ImportIllustration.animatedMixin}
      }
    `}
`;

const Input = styled.input`
  position: absolute;
  opacity: 0;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  cursor: ${({readOnly}) => (readOnly ? 'not-allowed' : 'pointer')};
`;

const MediaFileLabel = styled.div`
  font-size: ${getFontSize('default')};
  font-weight: normal;
  color: ${getColor('grey', 140)};
  flex-grow: 1;
  display: flex;
  align-items: flex-end;
  text-overflow: ellipsis;
  white-space: nowrap;
  overflow: hidden;
`;

const MediaFilePlaceholder = styled(MediaFileLabel)`
  color: ${getColor('grey', 100)};
`;

const ReadOnlyIcon = styled(LockIcon)`
  padding: 4px;
`;

const ActionContainer = styled.div<{isCompact: boolean} & AkeneoThemedProps>`
  ${({isCompact}) =>
    !isCompact &&
    css`
      position: absolute;
      top: 8px;
      right: 8px;
    `}

  display: flex;
  gap: 2px;
  align-items: center;
  color: ${getColor('grey', 100)};
`;

const UploadProgress = styled(ProgressBar)`
  flex: 1;
  width: 100%;
`;

const MediaFileImage = styled(Image)`
  border: none;
`;

type PreviewType = 'preview' | 'thumbnail';

type MediaFileInputProps = Override<
  Override<React.InputHTMLAttributes<HTMLInputElement>, InputProps<FileInfo | null>>,
  (
    | {
        readOnly: true;
      }
    | {
        readOnly?: boolean;
        onChange: (newValue: FileInfo | null) => void;
      }
  ) & {
    /**
     * Value of the input.
     */
    value: FileInfo | null;

    /**
     * Method called to generate the file preview url (can be base64).
     */
    previewer: (value: FileInfo, type: PreviewType) => string;

    /**
     * Method called to upload the file.
     */
    uploader: (file: File, onProgress: (ratio: number) => void) => Promise<FileInfo>;

    /**
     * Method called to generate the file download url.
     */
    downloader: (value: FileInfo) => string;

    /**
     * Placeholder displayed when the input is empty.
     */
    placeholder?: string;

    /**
     * Label displayed during image uploading.
     */
    uploadingLabel: string;

    /**
     * Label of the download button in the fullscreen preview and title of the download icon button.
     */
    downloadLabel: string;

    /**
     * Title of the clear icon button.
     */
    clearTitle: string;

    /**
     * Title of the fullscreen icon button.
     */
    fullscreenTitle: string;

    /**
     * Label displayed at the top of the fullscreen preview.
     */
    fullscreenLabel?: string;

    /**
     * Title of the close icon button in the fullscreen preview.
     */
    closeTitle: string;

    /**
     * Label displayed when the upload failed.
     */
    uploadErrorLabel: string;

    /**
     * Defines if the input is compact or not.
     */
    size?: 'default' | 'small';

    /**
     * Defines if the input is valid on not.
     */
    invalid?: boolean;
  }
>;

/**
 * Media File input allows the user to enter content when the expected user input is a file.
 */
const MediaFileInput = React.forwardRef<HTMLInputElement, MediaFileInputProps>(
  (
    {
      onChange,
      value,
      previewer,
      uploadingLabel,
      uploader,
      downloader,
      size = 'default',
      placeholder,
      downloadLabel,
      fullscreenTitle,
      fullscreenLabel,
      clearTitle,
      closeTitle,
      uploadErrorLabel,
      invalid = false,
      readOnly = false,
      ...rest
    }: MediaFileInputProps,
    forwardedRef: Ref<HTMLInputElement>
  ) => {
    const containerRef = useRef<HTMLDivElement>(null);
    const internalInputRef = useRef<HTMLInputElement>(null);
    const isCompact = size === 'small';
    const [isUploading, startUploading, stopUploading] = useBooleanState(false);
    const [hasUploadFailed, uploadFailed, uploadSucceeded] = useBooleanState(false);
    const [progress, setProgress] = useState<number>(0);
    const [isFullScreenModalOpen, openFullScreenModal, closeFullScreenModal] = useBooleanState(false);
    const [thumbnailUrl, setThumbnailUrl] = useState<string | null>(null);
    forwardedRef = forwardedRef ?? internalInputRef;

    const openFileExplorer = () => {
      if (forwardedRef && 'function' !== typeof forwardedRef && forwardedRef.current && !readOnly && onChange) {
        forwardedRef.current.click();
      }
    };

    useEffect(() => {
      if (!value) {
        setThumbnailUrl(null);
      } else {
        setThumbnailUrl(previewer(value, 'thumbnail'));
      }
    }, [value]);

    const handleUpload = async (file: File) => {
      startUploading();

      try {
        const uploadedFile = await uploader(file, setProgress);
        uploadSucceeded();
        onChange?.(uploadedFile);
      } catch (error) {
        uploadFailed();
        console.error(error);
      } finally {
        setProgress(0);
        stopUploading();
      }
    };

    const handleChange = (event: React.ChangeEvent<HTMLInputElement>) => {
      event.preventDefault();
      event.stopPropagation();
      if (event.target.files) void handleUpload(event.target.files[0]);
    };
    const handleClear = () => !readOnly && onChange?.(null);

    useShortcut(Key.Enter, openFileExplorer, containerRef);

    return (
      <>
        <MediaFileInputContainer
          ref={containerRef}
          tabIndex={readOnly ? -1 : 0}
          invalid={invalid || hasUploadFailed}
          readOnly={readOnly}
          isCompact={isCompact}
        >
          {!value && (
            <Input
              ref={forwardedRef}
              type="file"
              onChange={handleChange}
              readOnly={readOnly}
              disabled={readOnly}
              placeholder={placeholder}
              {...rest}
            />
          )}
          {isUploading ? (
            <>
              <MediaFileImage
                height={isCompact ? 47 : 120}
                width={isCompact ? 47 : 120}
                src={null}
                alt={uploadingLabel}
              />
              <UploadProgress
                title={uploadingLabel}
                progressLabel={`${Math.round(progress * 100)}%`}
                level="primary"
                percent={progress * 100}
              />
            </>
          ) : null !== value ? (
            <>
              <Image
                height={isCompact ? 47 : 120}
                width={isCompact ? 47 : 120}
                src={thumbnailUrl}
                alt={value.originalFilename}
                onError={() => setThumbnailUrl(DefaultPictureIllustration)}
              />
              {readOnly ? (
                <MediaFilePlaceholder>{value.originalFilename}</MediaFilePlaceholder>
              ) : (
                <MediaFileLabel>{value.originalFilename}</MediaFileLabel>
              )}
            </>
          ) : (
            <>
              <ImportIllustration size={isCompact ? 47 : 180} />
              <MediaFilePlaceholder>{hasUploadFailed ? uploadErrorLabel : placeholder}</MediaFilePlaceholder>
            </>
          )}

          <ActionContainer isCompact={isCompact}>
            {value && (
              <>
                <ActionButton
                  size="small"
                  level="tertiary"
                  ghost="borderless"
                  icon={<FullscreenIcon />}
                  title={fullscreenTitle}
                  onClick={openFullScreenModal}
                />
                <ActionButton
                  size="small"
                  href={downloader(value)}
                  target="_blank"
                  download={value.originalFilename}
                  level="tertiary"
                  ghost="borderless"
                  icon={<DownloadIcon />}
                  title={downloadLabel}
                />
                {!readOnly && (
                  <ActionButton
                    size="small"
                    level="tertiary"
                    ghost="borderless"
                    icon={<CloseIcon />}
                    title={clearTitle}
                    onClick={handleClear}
                  />
                )}
              </>
            )}
            {readOnly && <ReadOnlyIcon size={16} />}
          </ActionContainer>
        </MediaFileInputContainer>
        {isFullScreenModalOpen && value && (
          <FullscreenPreview
            value={value}
            previewUrl={previewer(value, 'preview')}
            downloadUrl={downloader(value)}
            downloadLabel={downloadLabel}
            closeTitle={closeTitle}
            label={fullscreenLabel ?? value.originalFilename}
            onClose={closeFullScreenModal}
          />
        )}
      </>
    );
  }
);

export {MediaFileInput};
