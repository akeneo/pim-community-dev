import React from 'react';
import {MediaLinkInput} from './MediaLinkInput';
import {render, screen, fireEvent} from '../../../storybook/test-util';
import userEvent from '@testing-library/user-event';
import {IconButton} from '../../../components/IconButton/IconButton';
import {CopyIcon} from '../../../icons';

jest.mock('../../../../static/illustrations/DefaultPicture.svg', () => 'FALLBACK_IMAGE');

const defaultProps = {
  placeholder: 'Put your media link here',
  closeTitle: 'Close',
  fullscreenTitle: 'Fullscreen',
};

test('it renders an empty MediaLinkInput', () => {
  const handleChange = jest.fn();

  render(
    <MediaLinkInput
      {...defaultProps}
      value=""
      thumbnailUrl="some.jpg"
      preview={<img alt="nice image" />}
      onChange={handleChange}
    />
  );

  expect(screen.getByTitle(/Put your media link here/i)).toBeInTheDocument();
  expect(screen.getByPlaceholderText(/Put your media link here/i)).toBeInTheDocument();
});

test('it can handle change', () => {
  const handleChange = jest.fn();

  render(
    <MediaLinkInput
      {...defaultProps}
      value="some"
      thumbnailUrl="some.jpg"
      preview={<img alt="nice image" />}
      onChange={handleChange}
    >
      <IconButton icon={<CopyIcon />} title="Copy" />
    </MediaLinkInput>
  );

  userEvent.paste(screen.getByPlaceholderText(/Put your media link here/i), '-other.jpg');

  expect(handleChange).toHaveBeenCalledWith('some-other.jpg');

  expect(screen.getByTitle(/Copy/i)).toBeInTheDocument();
});

test('it displays Media link actions and the fullscreen preview when hitting the fullscreen button', () => {
  const handleChange = jest.fn();

  render(
    <MediaLinkInput
      {...defaultProps}
      value="some"
      thumbnailUrl="some.jpg"
      preview={<img alt="nice image" />}
      onChange={handleChange}
    >
      <IconButton icon={<CopyIcon />} title="Copy" />
    </MediaLinkInput>
  );

  const fullscreenButton = screen.getByTitle(/Fullscreen/i);

  expect(fullscreenButton).toBeInTheDocument();
  expect(screen.getByTitle(/Copy/i)).toBeInTheDocument();

  userEvent.click(fullscreenButton);

  expect(screen.getByAltText('nice image')).toBeInTheDocument();
});

test('it does not call onChange when read only', () => {
  const handleChange = jest.fn();

  render(
    <MediaLinkInput
      {...defaultProps}
      value="some"
      thumbnailUrl="some.jpg"
      preview={<img alt="nice image" />}
      onChange={handleChange}
      readOnly={true}
    >
      <IconButton icon={<CopyIcon />} title="Copy" />
    </MediaLinkInput>
  );

  userEvent.type(screen.getByPlaceholderText(/Put your media link here/i), '-other.jpg');

  expect(handleChange).not.toHaveBeenCalled();
});

test('it calls onSubmit handler when hitting the enter key', () => {
  const handleChange = jest.fn();
  const onSubmit = jest.fn();

  render(
    <MediaLinkInput
      {...defaultProps}
      value=""
      thumbnailUrl="some.jpg"
      preview={<img alt="nice image" />}
      onChange={handleChange}
      onSubmit={onSubmit}
    />
  );

  userEvent.type(screen.getByPlaceholderText(/Put your media link here/i), 'some-other.jpg{enter}');

  expect(onSubmit).toHaveBeenCalled();
});

test('it displays the default picture when the image previewer fails', () => {
  const handleChange = jest.fn();

  render(
    <MediaLinkInput
      {...defaultProps}
      value="some.jpg"
      thumbnailUrl="some.jpg"
      preview={<img alt="nice image" />}
      onChange={handleChange}
    />
  );

  const thumbnail = screen.getByAltText(/some.jpg/i);

  fireEvent.error(thumbnail);

  expect(thumbnail).toHaveAttribute('src', 'FALLBACK_IMAGE');
});

test('MediaLinkInput supports forwardRef', () => {
  const ref = {current: null};

  render(
    <MediaLinkInput
      {...defaultProps}
      value=""
      thumbnailUrl="some.jpg"
      preview={<img alt="nice image" />}
      onChange={jest.fn()}
      ref={ref}
    />
  );
  expect(ref.current).not.toBe(null);
});

test('MediaLinkInput supports ...rest props', () => {
  render(
    <MediaLinkInput
      {...defaultProps}
      value=""
      thumbnailUrl="some.jpg"
      preview={<img alt="nice image" />}
      onChange={jest.fn()}
      data-testid="my_value"
    />
  );
  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});
