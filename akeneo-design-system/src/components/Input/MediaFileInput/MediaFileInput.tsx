import React, {Ref, useEffect, useRef, useState} from 'react';
import styled, {css} from 'styled-components';
import {Key, Override} from '../../../shared';
import {InputProps} from '../InputProps';
import {AkeneoThemedProps, getColor, getFontSize} from '../../../theme';
import {ImportIllustration} from '../../../illustrations';
import {IconButton, Image} from '../../../components';
import {CloseIcon, DownloadIcon, FullscreenIcon, LockIcon} from '../../../icons';
import {useBooleanState, useShortcut} from '../../../hooks';
import {FileInfo} from './FileInfo';
import {FullscreenPreview} from './FullscreenPreview';

const MediaFileInputContainer = styled.div<{isCompact: boolean} & AkeneoThemedProps>`
  position: relative;
  display: flex;
  flex-direction: ${({isCompact}) => (isCompact ? 'row' : 'column')};
  align-items: center;
  padding: ${({isCompact}) => (isCompact ? 12 : 20)}px;
  border: 1px solid ${({invalid}) => (invalid ? getColor('red', 100) : getColor('grey', 80))};
  border-radius: 2px;
  height: ${({isCompact}) => (isCompact ? 74 : 180)}px;
  gap: 10px;
  outline-style: none;
  box-sizing: border-box;
  background: ${({readOnly}) => (readOnly ? getColor('grey', 20) : getColor('white'))};

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

const MediaFileLabel = styled.div<{isEmpty: boolean} & AkeneoThemedProps>`
  font-size: ${getFontSize('default')};
  font-weight: normal;
  color: ${({isEmpty}) => getColor('grey', isEmpty ? 100 : 140)};
  flex-grow: 1;
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

const ActionButton = styled(IconButton)`
  color: ${getColor('grey', 100)};
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
     * Label of the download button.
     */
    downloadLabel: string;

    /**
     * Title of the clear button.
     */
    clearTitle: string;

    /**
     * Title of the fullscreen button.
     */
    fullscreenTitle: string;

    /**
     * Label of the Media File in the fullscreen preview.
     */
    fullscreenLabel?: string;

    /**
     * Title of the Close fullscreen preview button.
     */
    closeTitle: string;

    /**
     * Defines if the input is compact or not.
     */
    size: 'default' | 'small';

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
      alt,
      onChange,
      value,
      previewer,
      uploadingLabel,
      uploader,
      downloader,
      size,
      placeholder,
      downloadLabel,
      fullscreenTitle,
      fullscreenLabel,
      clearTitle,
      closeTitle,
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
      if (!onChange || readOnly) return;

      startUploading();

      try {
        const uploadedFile = await uploader(file, console.log);
        onChange(uploadedFile);
      } catch (error) {
        console.error(error);
      } finally {
        stopUploading();
      }
    };

    const handleChange = (event: React.ChangeEvent<HTMLInputElement>) => {
      event.preventDefault();
      event.stopPropagation();
      if (event.target.files) handleUpload(event.target.files[0]);
    };
    const handleClear = () => !readOnly && onChange?.(null);

    useShortcut(Key.Enter, openFileExplorer, containerRef);

    return (
      <>
        <MediaFileInputContainer
          ref={containerRef}
          tabIndex={readOnly ? -1 : 0}
          invalid={invalid}
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
              {...rest}
            />
          )}
          {thumbnailUrl ? (
            <Image height={isCompact ? 47 : 120} width={isCompact ? 47 : 120} src={thumbnailUrl} alt={alt ?? ''} />
          ) : (
            <ImportIllustration size={isCompact ? 47 : 180} />
          )}
          <MediaFileLabel isEmpty={isUploading || !value}>
            {isUploading ? uploadingLabel : value ? value.originalFilename : placeholder}
          </MediaFileLabel>
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
