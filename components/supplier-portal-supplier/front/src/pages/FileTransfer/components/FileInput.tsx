import React, {ReactElement, Ref, useRef, useState} from 'react';
import styled from 'styled-components';
import {
    AkeneoThemedProps,
    Button,
    FileInfo,
    getColor,
    getFontSize,
    Helper,
    InputProps,
    Key,
    Override,
    ProgressBar,
    UploadIcon,
    useBooleanState,
    useShortcut,
} from 'akeneo-design-system';

type FileInputProps = Override<
    Override<React.InputHTMLAttributes<HTMLInputElement>, InputProps<FileInfo | null>>,
    {
        onChange: (newValue: FileInfo | null) => void;
        value: FileInfo | null;
        uploader: (file: File, onProgress: (ratio: number) => void) => Promise<FileInfo>;
        placeholder: string | ReactElement;
        uploadButtonLabel: string;
        uploadingLabel: string;
        fileDraggingLabel: string;
        uploadingPlaceholder: string;
        uploadErrorLabel: string;
        invalid?: boolean;
    }
>;

const FileInput = React.forwardRef<HTMLInputElement, FileInputProps>(
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
            className,
            ...rest
        }: FileInputProps,
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
            if (forwardedRef && 'function' !== typeof forwardedRef && forwardedRef.current && onChange) {
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
            uploadSucceeded();
            if (event.target.files) void handleUpload(event.target.files[0]);
        };

        useShortcut(Key.Enter, openFileExplorer, containerRef);

        return (
            <>
                {isDraggingFile && <Overlay />}

                <FileInputContainer
                    ref={containerRef}
                    tabIndex={0}
                    invalid={invalid || hasUploadFailed}
                    className={className}
                    isDragging={isDraggingFile}
                >
                    {!isUploading && (
                        <>
                            <Input
                                ref={forwardedRef}
                                type="file"
                                onChange={handleChange}
                                onDragOver={() => setIsDraggingFile(true)}
                                onDragLeave={() => setIsDraggingFile(false)}
                                data-testid="file-input"
                                {...rest}
                            />
                            {!isDraggingFile && (
                                <>
                                    <MediaFilePlaceholder>
                                        <>
                                            <Placeholder>{placeholder}</Placeholder>
                                            <UploadButton level="secondary">
                                                <UploadIcon size={16} />
                                                {uploadButtonLabel}
                                            </UploadButton>
                                        </>
                                    </MediaFilePlaceholder>
                                    {hasUploadFailed && (
                                        <Helper inline={true} level="error">
                                            {uploadErrorLabel}
                                        </Helper>
                                    )}
                                </>
                            )}
                            {isDraggingFile && <FileDraggingMask>{fileDraggingLabel}</FileDraggingMask>}
                        </>
                    )}
                    {isUploading && (
                        <>
                            <UploadingPlaceholder>{uploadingPlaceholder}</UploadingPlaceholder>
                            <UploadProgress
                                title={uploadingLabel}
                                progressLabel={`${Math.round(progress * 100)}%`}
                                level="primary"
                                percent={progress * 100}
                            />
                        </>
                    )}
                </FileInputContainer>
            </>
        );
    }
);
const FileInputContainer = styled.div<{isDragging: boolean} & AkeneoThemedProps>`
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 20px;
    background-image: ${({isDragging}) =>
        `url("data:image/svg+xml,%3csvg width='100%25' height='100%25' xmlns='http://www.w3.org/2000/svg'%3e%3crect width='100%25' height='100%25' rx='2' ry='2' fill='none' stroke='%23${
            isDragging ? '11324D' : 'C7CBD4'
        }FF' stroke-width='2' stroke-dasharray='10' stroke-dashoffset='0' stroke-linecap='butt'/%3e%3c/svg%3e")`};
    border-radius: 2px;
    height: 400px;
    outline-style: none;
    box-sizing: border-box;
    background-color: ${({isDragging}) => getColor(isDragging ? 'brand20' : 'blue10')};
    overflow: hidden;

    &:focus {
        box-shadow: 0 0 0 2px ${getColor('blue', 40)};
    }
`;

const Input = styled.input`
    position: absolute;
    opacity: 0;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    cursor: pointer;
`;

const MediaFilePlaceholder = styled.div`
    font-size: ${getFontSize('default')};
    font-weight: normal;
    color: ${getColor('grey', 100)};
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

const UploadingPlaceholder = styled.div`
    color: ${getColor('grey', 120)};
    margin: auto;
`;

const UploadProgress = styled(ProgressBar)`
    width: 100%;
`;

const UploadButton = styled(Button)`
    height: 38px;
    border-radius: 19px;
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

const Overlay = styled.div`
    position: fixed;
    width: 100vw;
    height: 100vh;
    top: 0;
    left: 0;
    background-color: ${getColor('white')};
    opacity: 0.5;
`;

export {FileInput};
