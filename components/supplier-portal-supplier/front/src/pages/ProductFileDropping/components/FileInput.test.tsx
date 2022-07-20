import React from 'react';
import {FileInput} from './FileInput';
import {act, fireEvent, screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from '../../../tests';
import {BadRequestError} from '../../../api/BadRequestError';

const flushPromises = () => new Promise(setImmediate);

const defaultProps = {
    placeholder: 'Drag and drop your file to launch the upload',
    uploadingLabel: 'Uploading...',
    uploadingPlaceholder: 'It will be ready soon',
    uploadErrorLabel: 'There was an error while uploading your file. please try again.',
    fileDraggingLabel: 'Drag & drop your file here',
    uploadButtonLabel: 'Browse your files',
    generateUploadSuccessLabel: (filename: string) => `${filename} was sucessfully shared`,
};

const excelFile = new File(['foo'], 'foo.xlsx', {
    type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
});

test('it displays an input file and a placeholder', async () => {
    renderWithProviders(<FileInput {...defaultProps} onFileUploaded={jest.fn()} uploader={jest.fn()} />);

    expect(screen.getByTestId('file-input')).toBeInTheDocument();
    expect(screen.getByText('Drag and drop your file to launch the upload')).toBeInTheDocument();
    expect(screen.getByText('Browse your files')).toBeInTheDocument();
});

test('it displays a message when a user drags a file onto the component to upload it', async () => {
    renderWithProviders(<FileInput {...defaultProps} onFileUploaded={jest.fn()} uploader={jest.fn()} />);

    await act(async () => {
        fireEvent.dragOver(screen.getByTestId('file-input'));
    });
    expect(screen.getByText('Drag & drop your file here')).toBeInTheDocument();
});

test('it can upload a file', async () => {
    const onFileUploaded = jest.fn();
    const uploader = jest.fn().mockResolvedValue({});

    renderWithProviders(<FileInput {...defaultProps} onFileUploaded={onFileUploaded} uploader={uploader} />);

    await act(async () => {
        userEvent.upload(screen.getByTestId('file-input'), excelFile);
        await flushPromises();
    });

    expect(onFileUploaded).toHaveBeenCalledWith(true);
    expect(screen.getByText('foo.xlsx was sucessfully shared')).toBeInTheDocument();
});

test('it can open the file upload explorer using the keyboard', async () => {
    const onFileUploaded = jest.fn();
    const uploader = jest.fn().mockResolvedValue({});

    renderWithProviders(<FileInput {...defaultProps} onFileUploaded={onFileUploaded} uploader={uploader} />);

    const fileInput = screen.getByTestId('file-input');

    await act(async () => {
        fireEvent.focus(fileInput);
        userEvent.type(fileInput, '{enter}');
        userEvent.upload(fileInput, excelFile);
        await flushPromises();
    });

    expect(onFileUploaded).toHaveBeenCalled();
    expect(screen.getByText('foo.xlsx was sucessfully shared')).toBeInTheDocument();
});

test('it displays the upload error label when the upload failed', async () => {
    const onFileUploaded = jest.fn();
    const uploader = jest.fn().mockRejectedValue(new BadRequestError({error: 'bad file'}));

    renderWithProviders(<FileInput {...defaultProps} onFileUploaded={onFileUploaded} uploader={uploader} />);

    const fileInput = screen.getByTestId('file-input');

    await act(async () => {
        userEvent.upload(fileInput, excelFile);
        await flushPromises();
    });

    expect(onFileUploaded).toHaveBeenCalledWith(false);
    expect(screen.getByText('bad file')).toBeInTheDocument();
});

test('it does not upload the file if the file is not an excel file', async () => {
    const onFileUploaded = jest.fn();
    const uploader = jest.fn();

    renderWithProviders(<FileInput {...defaultProps} onFileUploaded={onFileUploaded} uploader={uploader} />);

    const file = new File(['foo'], 'foo.png', {type: 'image/png'});

    await act(async () => {
        userEvent.upload(screen.getByTestId('file-input'), file);
        await flushPromises();
    });

    expect(onFileUploaded).toHaveBeenCalledWith(false);
    expect(uploader).not.toHaveBeenCalled();
    expect(screen.getByText('This file is not a xlsx file.')).toBeInTheDocument();
});
