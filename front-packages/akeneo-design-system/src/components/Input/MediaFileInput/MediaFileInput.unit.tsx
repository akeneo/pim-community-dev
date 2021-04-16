import React from 'react';
import {MediaFileInput} from './MediaFileInput';
import {act, fireEvent, render, screen} from '../../../storybook/test-util';
import userEvent from '@testing-library/user-event';
import {FileInfo} from './FileInfo';
import {DownloadIcon} from '../../../icons';
import {IconButton} from '../../IconButton/IconButton';

jest.mock('../../../../static/illustrations/DefaultPicture.svg', () => 'FALLBACK_IMAGE');

const flushPromises = () => new Promise(setImmediate);

const defaultProps = {
  placeholder: 'Upload your file here',
  clearTitle: 'Clear',
  uploadingLabel: 'Uploading...',
  uploadErrorLabel: 'An error occurred during upload',
};

const imageFile = new File(['foo'], 'foo.jpg', {type: 'image/jpeg'});
const fileInfo: FileInfo = {
  originalFilename: 'foo.jpg',
  filePath: 'path/to/foo.jpg',
};

test('it renders and handle changes', async () => {
  const handleChange = jest.fn();
  const uploader = jest.fn().mockResolvedValue(fileInfo);

  render(
    <MediaFileInput {...defaultProps} value={null} onChange={handleChange} uploader={uploader} thumbnailUrl={null} />
  );

  const fileInput = screen.getByPlaceholderText(/Upload your file here/i);

  expect(fileInput).toBeInTheDocument();

  await act(async () => {
    userEvent.upload(fileInput, imageFile);
    await flushPromises();
  });

  expect(handleChange).toHaveBeenCalledWith(fileInfo);
});

test('it does not display invalid children', () => {
  const handleChange = jest.fn();
  const uploader = jest.fn();

  render(
    <MediaFileInput {...defaultProps} value={null} onChange={handleChange} uploader={uploader} thumbnailUrl={null}>
      <span>not valid child</span>
    </MediaFileInput>
  );

  expect(screen.queryByText(/not valid child/i)).not.toBeInTheDocument();
});

test('it can open the file upload explorer using the keyboard', async () => {
  const handleChange = jest.fn();
  const uploader = jest.fn().mockResolvedValue(fileInfo);

  render(
    <MediaFileInput
      {...defaultProps}
      value={null}
      onChange={handleChange}
      uploader={uploader}
      thumbnailUrl={null}
      size="small"
    />
  );

  const fileInput = screen.getByPlaceholderText(/Upload your file here/i);

  expect(fileInput).toBeInTheDocument();

  await act(async () => {
    fireEvent.focus(fileInput);
    userEvent.type(fileInput, '{enter}');
    userEvent.upload(fileInput, imageFile);
    await flushPromises();
  });

  expect(handleChange).toHaveBeenCalledWith(fileInfo);
});

test('it does not call onChange if readOnly', async () => {
  const handleChange = jest.fn();
  const uploader = jest.fn().mockResolvedValue(fileInfo);

  render(
    <MediaFileInput
      {...defaultProps}
      value={null}
      onChange={handleChange}
      uploader={uploader}
      thumbnailUrl={null}
      readOnly={true}
    />
  );

  const fileInput = screen.getByPlaceholderText(/Upload your file here/i);

  expect(fileInput).toBeInTheDocument();

  await act(async () => {
    userEvent.upload(fileInput, imageFile);
    await flushPromises();
  });

  expect(handleChange).not.toHaveBeenCalled();
});

test('it display the upload error label when the upload failed', async () => {
  const handleChange = jest.fn();
  const uploader = jest.fn().mockRejectedValue(undefined);

  jest.spyOn(console, 'error').mockImplementationOnce(jest.fn());

  render(
    <MediaFileInput
      {...defaultProps}
      value={null}
      onChange={handleChange}
      uploader={uploader}
      thumbnailUrl={null}
      size="small"
    />
  );

  const fileInput = screen.getByPlaceholderText(/Upload your file here/i);

  expect(fileInput).toBeInTheDocument();

  await act(async () => {
    userEvent.upload(fileInput, imageFile);
    await flushPromises();
  });

  expect(handleChange).not.toHaveBeenCalled();
  expect(screen.getByText(/An error occurred during upload/i)).toBeInTheDocument();
});

test('it displays the preview and action buttons when the value is not null', () => {
  const handleChange = jest.fn();
  const uploader = jest.fn().mockResolvedValue(fileInfo);
  const thumbnailUrl = `https://${fileInfo.filePath}`;

  render(
    <MediaFileInput
      {...defaultProps}
      value={fileInfo}
      onChange={handleChange}
      uploader={uploader}
      thumbnailUrl={thumbnailUrl}
    >
      <IconButton
        href={thumbnailUrl}
        target="_blank"
        download={thumbnailUrl}
        icon={<DownloadIcon />}
        title="Download"
      />
    </MediaFileInput>
  );

  expect(screen.getByTitle(/Download/i)).toBeInTheDocument();
  expect(screen.getByTitle(/Clear/i)).toBeInTheDocument();
  expect(screen.getByAltText(fileInfo.originalFilename)).toBeInTheDocument();
});

test('it clears the value when clicking on the clear button', () => {
  const handleChange = jest.fn();
  const uploader = jest.fn().mockResolvedValue(fileInfo);
  const thumbnailUrl = `https://${fileInfo.filePath}`;

  render(
    <MediaFileInput
      {...defaultProps}
      value={fileInfo}
      onChange={handleChange}
      uploader={uploader}
      thumbnailUrl={thumbnailUrl}
    />
  );

  userEvent.click(screen.getByTitle(/Clear/i));

  expect(handleChange).toHaveBeenCalledWith(null);
});

test('it displays the default picture when the image previewer fails', () => {
  const handleChange = jest.fn();
  const uploader = jest.fn().mockResolvedValue(fileInfo);
  const thumbnailUrl = `https://${fileInfo.filePath}`;

  render(
    <MediaFileInput
      {...defaultProps}
      value={fileInfo}
      onChange={handleChange}
      uploader={uploader}
      thumbnailUrl={thumbnailUrl}
    />
  );

  const thumbnail = screen.getByAltText(fileInfo.originalFilename);

  fireEvent.error(thumbnail);

  expect(thumbnail).toHaveAttribute('src', 'FALLBACK_IMAGE');
});

test('MediaFileInput supports forwardRef', () => {
  const handleChange = jest.fn();
  const uploader = jest.fn().mockResolvedValue(fileInfo);
  const ref = {current: null};

  render(
    <MediaFileInput
      {...defaultProps}
      value={null}
      onChange={handleChange}
      uploader={uploader}
      thumbnailUrl={null}
      ref={ref}
    />
  );

  expect(ref.current).not.toBe(null);
});

test('MediaFileInput supports ...rest props', () => {
  const handleChange = jest.fn();
  const uploader = jest.fn().mockResolvedValue(fileInfo);

  render(
    <MediaFileInput
      {...defaultProps}
      value={null}
      onChange={handleChange}
      uploader={uploader}
      thumbnailUrl={null}
      data-testid="my_value"
    />
  );

  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});
