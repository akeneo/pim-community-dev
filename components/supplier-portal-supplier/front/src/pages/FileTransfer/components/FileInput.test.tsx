import React from 'react';
import {FileInput} from './FileInput';
import {act, fireEvent, screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {FileInfo} from 'akeneo-design-system';
import {renderWithProviders} from '../../../tests';

const flushPromises = () => new Promise(setImmediate);

const defaultProps = {
    placeholder: 'Drag and drop your file to launch the upload',
    uploadingLabel: 'Uploading...',
    uploadingPlaceholder: 'It will be ready soon',
    uploadErrorLabel: 'There was an error while uploading your file. please try again.',
    fileDraggingLabel: 'Drag & drop your file here',
    uploadButtonLabel: 'Browse your files',
};

const excelFile = new File(['foo'], 'foo.xlsx', {
    type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
});
const fileInfo: FileInfo = {
    originalFilename: 'foo.jpg',
    filePath: 'path/to/foo.jpg',
};

test('it displays an input file and a placeholder', async () => {
    renderWithProviders(<FileInput {...defaultProps} value={null} onChange={jest.fn()} uploader={jest.fn()} />);

    expect(screen.getByTestId('file-input')).toBeInTheDocument();
    expect(screen.getByText('Drag and drop your file to launch the upload')).toBeInTheDocument();
    expect(screen.getByText('Browse your files')).toBeInTheDocument();
});

test('it displays a message when a user drags a file onto the component to upload it', async () => {
    renderWithProviders(<FileInput {...defaultProps} value={null} onChange={jest.fn()} uploader={jest.fn()} />);

    await act(async () => {
        fireEvent.dragOver(screen.getByTestId('file-input'));
    });
    expect(screen.getByText('Drag & drop your file here')).toBeInTheDocument();
});

test('it supports file upload', async () => {
    const handleChange = jest.fn();
    const uploader = jest.fn().mockResolvedValue(fileInfo);

    renderWithProviders(<FileInput {...defaultProps} value={null} onChange={handleChange} uploader={uploader} />);

    await act(async () => {
        userEvent.upload(screen.getByTestId('file-input'), excelFile);
        await flushPromises();
    });

    expect(handleChange).toHaveBeenCalledWith(fileInfo);
});

test('it can open the file upload explorer using the keyboard', async () => {
    const handleChange = jest.fn();
    const uploader = jest.fn().mockResolvedValue(fileInfo);

    renderWithProviders(<FileInput {...defaultProps} value={null} onChange={handleChange} uploader={uploader} />);

    const fileInput = screen.getByTestId('file-input');

    await act(async () => {
        fireEvent.focus(fileInput);
        userEvent.type(fileInput, '{enter}');
        userEvent.upload(fileInput, excelFile);
        await flushPromises();
    });

    expect(handleChange).toHaveBeenCalledWith(fileInfo);
});

test('it displays the upload error label when the upload failed', async () => {
    const handleChange = jest.fn();
    const uploader = jest.fn().mockRejectedValue(undefined);

    jest.spyOn(console, 'error').mockImplementationOnce(jest.fn());

    renderWithProviders(<FileInput {...defaultProps} value={null} onChange={handleChange} uploader={uploader} />);

    const fileInput = screen.getByTestId('file-input');

    await act(async () => {
        userEvent.upload(fileInput, excelFile);
        await flushPromises();
    });

    expect(handleChange).not.toHaveBeenCalled();
    expect(screen.getByText(/There was an error while uploading your file. please try again./i)).toBeInTheDocument();
});
