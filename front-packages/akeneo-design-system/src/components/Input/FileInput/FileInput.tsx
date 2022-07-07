import React, {ReactElement, Ref, useRef, useState} from 'react';
import styled, {css} from 'styled-components';
import {Key, Override} from '../../../shared';
import {InputProps} from '../common';
import {AkeneoThemedProps, getColor, getFontSize} from '../../../theme';
import {ImportIllustration} from '../../../illustrations';
import {ProgressBar} from '../../ProgressBar/ProgressBar';
import {useBooleanState, useShortcut} from '../../../hooks';
import {FileInfo} from './FileInfo';
import {Button} from "../../Button/Button";
import {UploadIcon} from "../../../icons";

const FileInputContainer = styled.div<{readOnly: boolean; isDragging: boolean} & AkeneoThemedProps>`
  position: relative;
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 30px;
  border: 1px dashed ${({invalid, isDragging}) => (invalid ? getColor('red', 100) : getColor(isDragging ? 'grey140' : 'grey80'))};
  border-radius: 2px;
  height: 400px;
  outline-style: none;
  box-sizing: border-box;
  background: ${({readOnly, isDragging}) => (readOnly ? getColor('grey20') : getColor(isDragging ? 'brand20' : 'blue10'))};
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

const FileLabel = styled.div`
  font-size: ${getFontSize('default')};
  font-weight: normal;
  color: ${getColor('grey', 140)};
  flex-grow: 1;
  text-overflow: ellipsis;
  white-space: nowrap;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 10px;
`;

const MediaFilePlaceholder = styled(FileLabel)`
  color: ${getColor('grey', 100)};
`;

const UploadingPlaceholder = styled.div`
  color: ${getColor('grey', 120)};
  margin: auto;
`;

const UploadProgress = styled(ProgressBar)`
  width: 100%;
`;

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
     * Method called to upload the file.
     */
    uploader: (file: File, onProgress: (ratio: number) => void) => Promise<FileInfo>;

    /**
     * Placeholder displayed when the input is empty.
     */
    placeholder?: string | ReactElement;

    /**
     * Label of the upload button
     */
    uploadButtonLabel?: string;

    /**
     * Label displayed during file uploading.
     */
    uploadingLabel: string;

    /**
     * Label displayed during file dragging over the input.
     */
    fileDraggingLabel: string;

    /**
     * Placeholder displayed when file is uploading
     */
    uploadingPlaceholder: string;

    /**
     * Label displayed when the upload failed.
     */
    uploadErrorLabel: string;

    /**
     * Defines if the input is valid on not.
     */
    invalid?: boolean;
  }
>;

const FileInput = React.forwardRef<HTMLInputElement, MediaFileInputProps>(
  (
    {
      onChange,
      value,
      uploadingLabel,
      uploadingPlaceholder,
      uploadButtonLabel,
      fileDraggingLabel,
      uploader,
      placeholder,
      uploadErrorLabel,
      invalid = false,
      readOnly = false,
      className,
      ...rest
    }: MediaFileInputProps,
    forwardedRef: Ref<HTMLInputElement>
  ) => {
    const containerRef = useRef<HTMLDivElement>(null);
    const internalInputRef = useRef<HTMLInputElement>(null);
    const [isUploading, startUploading, stopUploading] = useBooleanState(false);
    const [hasUploadFailed, uploadFailed, uploadSucceeded] = useBooleanState(false);
    const [isDraggingFile, setIsDraggingFile] = useState(false);
    const [progress, setProgress] = useState<number>(0);
    forwardedRef = forwardedRef ?? internalInputRef;

    const openFileExplorer = () => {
      if (forwardedRef && 'function' !== typeof forwardedRef && forwardedRef.current && !readOnly && onChange) {
        forwardedRef.current.click();
      }
    };

    const handleUpload = async (file: File) => {
      startUploading();
      setIsDraggingFile(false);

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

    useShortcut(Key.Enter, openFileExplorer, containerRef);

    return (
      <FileInputContainer
        ref={containerRef}
        tabIndex={readOnly ? -1 : 0}
        invalid={invalid || hasUploadFailed}
        readOnly={readOnly}
        className={className}
        isDragging={isDraggingFile}
      >
        {!isUploading && (
            <>
              <Input
                ref={forwardedRef}
                type="file"
                onChange={handleChange}
                readOnly={readOnly}
                disabled={readOnly}
                onDragOver={() => setIsDraggingFile(true)}
                onDragLeave={() => setIsDraggingFile(false)}
                {...rest}
              />
                {isDraggingFile && <FileDraggingMask>{fileDraggingLabel}</FileDraggingMask>}
            </>
        )}
        {isUploading ? (
            <>
                <UploadingPlaceholder>{uploadingPlaceholder}</UploadingPlaceholder>
            <UploadProgress
                title={uploadingLabel}
                progressLabel={`${Math.round(progress * 100)}%`}
                level="primary"
                percent={progress * 100}
            />
            </>
        ) : null !== value && readOnly ? (
            <MediaFilePlaceholder>{value.originalFilename}</MediaFilePlaceholder>
        ) : !isDraggingFile && (
            <MediaFilePlaceholder>
                {hasUploadFailed ?
                    uploadErrorLabel :
                    <>
                        <Placeholder>{placeholder}</Placeholder>
                        <UploadButton level="secondary">
                            <UploadIcon size={16}/>
                            {uploadButtonLabel}
                        </UploadButton>
                    </>
                }
            </MediaFilePlaceholder>
        )}
      </FileInputContainer>
    );
  }
);

const UploadButton = styled(Button)`
    height: 38px;
`;

const Placeholder = styled.div`
    color: ${getColor('grey120')};
`;

const FileDraggingMask = styled.div`
    color: ${getColor('grey140')};
  font-size: ${getFontSize('big')};
  font-weight: bold;
  margin: auto;
`;

export {FileInput};
