import React, {Ref, useRef} from 'react';
import styled, {css} from 'styled-components';
import {Key, Override} from '../../../shared';
import {InputProps} from '../InputProps';
import {AkeneoThemedProps, getColor, getFontSize} from '../../../theme';
import {ImportIllustration} from '../../../illustrations';
import {Image} from '../../Image/Image';
import {IconButton} from '../../IconButton/IconButton';
import {CloseIcon, DownloadIcon, FullscreenIcon, LockIcon} from '../../../icons';
import {useBooleanState, useShortcut} from '../../../hooks';

/**
 * value => ?FileInfo
 * previewer => (fileInfo: FileInfo) => string
 * onUpload => (file: File, updateProgress: (percent) => void) => Promise<FileInfo>
 * onChange => FileInfo
 */


const FileInputContainer = styled.div<{isCompact: boolean} & AkeneoThemedProps>`
  position: relative;
  display: flex;
  flex-direction: ${({isCompact}) => (isCompact ? 'row' : 'column')};
  align-items: center;
  padding: ${({isCompact}) => (isCompact ? '12px' : '20px')};
  border: 1px solid ${({invalid}) => (invalid ? getColor('red', 100) : getColor('grey', 80))};
  border-radius: 2px;
  height: ${({isCompact}) => (isCompact ? '74px' : '180px')};
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

const MediaFileLabelPlaceholder = styled.div<{isEmpty: boolean} & AkeneoThemedProps>`
  font-size: ${getFontSize('default')};
  font-weight: normal;
  color: ${({isEmpty}) => getColor('grey', isEmpty ? 100 : 140)};
  flex-grow: 1;
`;

const ActionContainer = styled.div<{isCompact: boolean} & AkeneoThemedProps>`
  position: absolute;
  top: 12px;
  right: 12px;
  display: flex;
  gap: 2px;
  align-items: center;
  color: ${getColor('grey', 100)};
`;

const ActionButton = styled(IconButton)`
  color: ${getColor('grey', 100)};
`;

type FileInputProps = Override<
  Override<React.InputHTMLAttributes<HTMLInputElement>, InputProps<File | null>>,
  (
    | {
        readOnly: true;
      }
    | {
        readOnly?: boolean;
        onChange: (newValue: File | null) => void;
      }
  ) & {
    /**
     * Value of the input.
     */
    value?: File;

    /**
     * The image to display in preview
     */
    previewSrc: string;

    /**
     * Placeholder displayed when the input is empty.
     */
    placeholder?: string;

    /**
     * Label displayed during image uploading.
     */
    uploadingLabel: string;

    /**
     * Title of the fullscreen button.
     */
    fullscreenTitle: string;

    /**
     * The handler called when user want to display preview in fullscreen.
     */
    onFullScreen: () => void;

    /**
     * Title of the download button.
     */
    downloadTitle: string;

    /**
     * Title of the clear button.
     */
    clearTitle: string;

    /**
     * The handler called when user want to download the preview.
     */
    onDownload: () => void;

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

/** TODO how to handle multiple files (asset upload) => another input (like multiselect) or same component ? */

/**
 * File input allows the user to enter content when the expected user input is a file.
 */
const FileInput = React.forwardRef<HTMLInputElement, FileInputProps>(
  (
    {
      alt,
      onChange,
      value,
      previewSrc,
      uploadingLabel,
      size,
      placeholder,
      downloadTitle,
      fullscreenTitle,
      onDownload,
      onFullScreen,
      clearTitle,
      invalid = false,
      readOnly = false,
      ...rest
    }: FileInputProps,
    forwardedRef: Ref<HTMLInputElement>
  ) => {
    const containerRef = useRef<HTMLDivElement>(null);
    const internalInputRef = useRef<HTMLInputElement>(null);
    const isCompact = size === 'small';
    const [isUploading, startUploading, stopUploading] = useBooleanState(false);
    forwardedRef = forwardedRef ?? internalInputRef;

    const openFileExplorer = () => {
      if (forwardedRef && 'function' !== typeof forwardedRef && forwardedRef.current && !readOnly && onChange) {
        forwardedRef.current.click();
      }
    };

    const handleDrop = (e: React.DragEvent<HTMLInputElement>) => onChange?.(e.dataTransfer.files[0]);
    const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => e.target.files && onChange?.(e.target.files[0]);
    const handleClear = () => !readOnly && onChange?.(null);

    useShortcut(Key.Enter, openFileExplorer, containerRef);

    return (
      <FileInputContainer
        ref={containerRef}
        tabIndex={readOnly ? -1 : 0}
        invalid={invalid}
        readOnly={readOnly}
        isCompact={isCompact}
      >
        <Input
          ref={forwardedRef}
          type="file"
          onChange={handleChange}
          readOnly={readOnly}
          disabled={readOnly}
          onDrop={handleDrop}
          {...rest}
        />
        {previewSrc ? (
          <Image
            onLoadStart={startUploading}
            onLoad={stopUploading}
            height={isCompact ? 47 : 120}
            width={isCompact ? 47 : 120}
            src={previewSrc}
            alt={alt ?? ''}
          />
        ) : (
          <ImportIllustration size={isCompact ? 74 : 180} />
        )}
        <MediaFileLabelPlaceholder isEmpty={isUploading || !value}>
          {isUploading ? uploadingLabel : value ? value.name : placeholder}
        </MediaFileLabelPlaceholder>
        <ActionContainer isCompact={isCompact}>
          {value && (
            <ActionButton
              level="tertiary"
              ghost="borderless"
              icon={<CloseIcon size={16} />}
              title={clearTitle}
              onClick={handleClear}
            />
          )}
          {value && (
            <ActionButton
              level="tertiary"
              ghost="borderless"
              icon={<DownloadIcon size={16} />}
              title={downloadTitle}
              onClick={onDownload}
            />
          )}
          {value && (
            <ActionButton
              level="tertiary"
              ghost="borderless"
              icon={<FullscreenIcon size={16} />}
              title={fullscreenTitle}
              onClick={onFullScreen}
            />
          )}
          {readOnly && <LockIcon size={16} />}
        </ActionContainer>
      </FileInputContainer>
    );
  }
);

export {FileInput};
