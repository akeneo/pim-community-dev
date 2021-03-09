import React from 'react';
import {TextAreaInput} from './TextAreaInput';
import {fireEvent, render, screen} from '../../../storybook/test-util';
import {ContentBlock} from 'draft-js';

jest.mock('html-to-draftjs', () => (text: string) =>
  'WILL FAIL' === text ? undefined : {contentBlocks: [new ContentBlock({text})]}
);

test('it renders and handle changes', () => {
  const handleChange = jest.fn();

  render(
    <>
      <label htmlFor="myInput">My label</label>
      <TextAreaInput id="myInput" value="Nice" onChange={handleChange} />
    </>
  );

  expect(screen.getByLabelText('My label')).toBeInTheDocument();
  const input = screen.getByLabelText('My label') as HTMLInputElement;
  fireEvent.change(input, {target: {value: 'Cool'}});
  expect(handleChange).toHaveBeenCalledWith('Cool');
});

test('it renders and does not call onChange if readOnly', () => {
  const handleChange = jest.fn();

  render(
    <>
      <label htmlFor="myInput">My label</label>
      <TextAreaInput id="myInput" readOnly={true} value="Nice" onChange={handleChange} />
    </>
  );

  expect(screen.getByLabelText('My label')).toBeInTheDocument();
  const input = screen.getByLabelText('My label') as HTMLInputElement;
  fireEvent.change(input, {target: {value: 'Cool'}});
  expect(handleChange).not.toHaveBeenCalledWith('Cool');
});

test('it renders and displays the character left label', () => {
  const handleChange = jest.fn();

  render(
    <>
      <label htmlFor="myInput">My label</label>
      <TextAreaInput
        id="myInput"
        characterLeftLabel="100 character remaining"
        readOnly={true}
        value="Nice"
        onChange={handleChange}
      />
    </>
  );

  expect(screen.getByText('100 character remaining')).toBeInTheDocument();
});

test('it renders rich text editor and handle changes', () => {
  const handleChange = jest.fn();

  render(
    <>
      <label htmlFor="myInput">My label</label>
      <TextAreaInput id="myInput" value="Nice RTF content" isRichText={true} onChange={handleChange} />
    </>
  );

  expect(screen.getByLabelText('rdw-wrapper')).toBeInTheDocument();
  expect(screen.getByText('Nice RTF content')).toBeInTheDocument();
  expect(screen.getByRole('textbox')).toBeInTheDocument();

  const input = screen.getByRole('textbox') as HTMLInputElement;

  // Hack to trigger onBeforeInput event listened by DraftJS
  fireEvent.paste(input, {
    clipboardData:{
      types: ['text/plain'],
      getData: () => 'New '
    }
  });

  expect(handleChange).toHaveBeenCalledWith('<p>New Nice RTF content</p>\n');
});

test('it renders an empty rich text editor when the value is unprocessable', () => {
  const handleChange = jest.fn();

  render(
    <>
      <label htmlFor="myInput">My label</label>
      <TextAreaInput id="myInput" value="WILL FAIL" isRichText={true} onChange={handleChange} />
    </>
  );

  expect(screen.queryByText('WILL FAIL')).not.toBeInTheDocument();
});

test('TextAreaInput supports forwardRef', () => {
  const ref = {current: null};

  render(<TextAreaInput value="nice" onChange={jest.fn()} ref={ref} />);
  expect(ref.current).not.toBe(null);
});

test('TextAreaInput supports ...rest props', () => {
  render(<TextAreaInput value="nice" onChange={jest.fn()} data-testid="my_value" />);
  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});
