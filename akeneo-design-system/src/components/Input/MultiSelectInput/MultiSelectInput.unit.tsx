import React from 'react';
import {MultiSelectInput} from './MultiSelectInput';
import {render, screen, fireEvent} from '../../../storybook/test-util';
import userEvent from '@testing-library/user-event';

test('it renders its children properly', () => {
  const onChange = jest.fn();
  render(
    <MultiSelectInput
      value={['en_US']}
      onChange={onChange}
      placeholder="Placeholder"
      removeLabel="Remove"
      emptyResultLabel="Empty result"
    >
      <MultiSelectInput.Option value="en_US">English</MultiSelectInput.Option>
      <MultiSelectInput.Option value="fr_FR">French</MultiSelectInput.Option>
      <MultiSelectInput.Option value="de_DE">German</MultiSelectInput.Option>
      <MultiSelectInput.Option value="es_ES">Spanish</MultiSelectInput.Option>
    </MultiSelectInput>
  );

  const input = screen.getByRole('textbox');
  fireEvent.focus(input);

  expect(screen.queryByText('German')).toBeInTheDocument();

  fireEvent.click(screen.getByTestId('backdrop'));
  expect(screen.queryByText('German')).not.toBeInTheDocument();

  fireEvent.focus(screen.getByRole('textbox'));
  expect(screen.queryByText('German')).toBeInTheDocument();

  const germanOption = screen.getByText('German');
  expect(germanOption).toBeInTheDocument();
  fireEvent.click(germanOption);
  expect(onChange).toHaveBeenCalledWith(['en_US', 'de_DE']);
});

test('it handles search', () => {
  const onChange = jest.fn();
  render(
    <MultiSelectInput
      value={['en_US']}
      onChange={onChange}
      placeholder="Placeholder"
      removeLabel="Remove"
      emptyResultLabel="Empty result"
    >
      <MultiSelectInput.Option value="en_US">English</MultiSelectInput.Option>
      <MultiSelectInput.Option value="fr_FR">French</MultiSelectInput.Option>
      <MultiSelectInput.Option value="de_DE">German</MultiSelectInput.Option>
      <MultiSelectInput.Option value="es_ES">Spanish</MultiSelectInput.Option>
    </MultiSelectInput>
  );

  const input = screen.getByRole('textbox');
  fireEvent.click(input);
  fireEvent.change(input, {target: {value: 'French'}});

  const germanOption = screen.queryByText('German');
  expect(germanOption).not.toBeInTheDocument();
  const frenchOption = screen.getByText('French');
  expect(frenchOption).toBeInTheDocument();
  fireEvent.keyDown(input, {key: 'Enter', code: 'Enter'});
  expect(onChange).toHaveBeenCalledWith(['en_US', 'fr_FR']);

  fireEvent.click(input);
  fireEvent.change(input, {target: {value: 'Spani'}});

  const spainOption = screen.getByText('Spanish');
  expect(spainOption).toBeInTheDocument();
  fireEvent.keyDown(input, {key: 'Enter', code: 'Enter'});
  expect(onChange).toHaveBeenCalledWith(['en_US', 'es_ES']);
  expect(onChange).toHaveBeenCalledTimes(2);
});

test('it handles empty cases', () => {
  const onChange = jest.fn();
  render(
    <MultiSelectInput
      value={[]}
      onChange={onChange}
      placeholder="Placeholder"
      removeLabel="Remove"
      emptyResultLabel="Empty result"
    >
      <MultiSelectInput.Option value="en_US">English</MultiSelectInput.Option>
      <MultiSelectInput.Option value="fr_FR">French</MultiSelectInput.Option>
      <MultiSelectInput.Option value="de_DE">German</MultiSelectInput.Option>
      <MultiSelectInput.Option value="es_ES">Spanish</MultiSelectInput.Option>
    </MultiSelectInput>
  );

  const input = screen.getByRole('textbox');
  fireEvent.click(input);
  fireEvent.change(input, {target: {value: 'France 3'}});

  const germanOption = screen.queryByText('German');
  expect(germanOption).not.toBeInTheDocument();
  const frenchOption = screen.queryByText('French');
  expect(frenchOption).not.toBeInTheDocument();
  expect(screen.getByText('Empty result')).toBeInTheDocument();

  fireEvent.keyDown(input, {key: 'Enter', code: 'Enter'});
  expect(onChange).not.toHaveBeenCalled();
});

test('it handles removing a Chip', () => {
  const onChange = jest.fn();
  render(
    <MultiSelectInput
      value={['en_US', 'fr_FR']}
      onChange={onChange}
      placeholder="Placeholder"
      removeLabel="Remove"
      emptyResultLabel="Empty result"
    >
      <MultiSelectInput.Option value="en_US">English</MultiSelectInput.Option>
      <MultiSelectInput.Option value="fr_FR">French</MultiSelectInput.Option>
      <MultiSelectInput.Option value="de_DE">German</MultiSelectInput.Option>
      <MultiSelectInput.Option value="es_ES">Spanish</MultiSelectInput.Option>
    </MultiSelectInput>
  );

  const clearButton = screen.getAllByTitle('Remove')[0];
  fireEvent.click(clearButton);

  expect(onChange).toHaveBeenCalledWith(['fr_FR']);
});

test('it handles keyboard events', () => {
  const onChange = jest.fn();
  render(
    <MultiSelectInput
      value={['en_US']}
      onChange={onChange}
      placeholder="Placeholder"
      removeLabel="Remove"
      emptyResultLabel="Empty result"
      openLabel="open"
    >
      <MultiSelectInput.Option value="en_US">English</MultiSelectInput.Option>
      <MultiSelectInput.Option value="fr_FR">French</MultiSelectInput.Option>
      <MultiSelectInput.Option value="de_DE">German</MultiSelectInput.Option>
      <MultiSelectInput.Option value="es_ES">Spanish</MultiSelectInput.Option>
    </MultiSelectInput>
  );

  const removeButton = screen.getByTitle('Remove');
  userEvent.type(removeButton, '{enter}');

  expect(onChange).toHaveBeenCalledWith([]);

  const openButton = screen.getByTitle('open');
  userEvent.type(openButton, '{enter}');

  const germanOption = screen.queryByText('German');
  expect(germanOption).toBeInTheDocument();
});

test('it closes the overlay when hitting Escape', () => {
  const onChange = jest.fn();
  render(
    <MultiSelectInput
      value={['en_US']}
      onChange={onChange}
      placeholder="Placeholder"
      removeLabel="Remove"
      emptyResultLabel="Empty result"
      openLabel="open"
    >
      <MultiSelectInput.Option value="en_US">English</MultiSelectInput.Option>
      <MultiSelectInput.Option value="fr_FR">French</MultiSelectInput.Option>
      <MultiSelectInput.Option value="de_DE">German</MultiSelectInput.Option>
      <MultiSelectInput.Option value="es_ES">Spanish</MultiSelectInput.Option>
    </MultiSelectInput>
  );

  const input = screen.getByRole('textbox');
  fireEvent.click(input);
  fireEvent.keyDown(input, {key: 'Escape', code: 'Escape'});

  const germanOption = screen.queryByText('German');
  expect(germanOption).not.toBeInTheDocument();
});

test('it can remove a chip using Backspace', () => {
  const onChange = jest.fn();
  render(
    <MultiSelectInput
      value={['en_US', 'fr_FR']}
      onChange={onChange}
      placeholder="Placeholder"
      removeLabel="Remove"
      emptyResultLabel="Empty result"
      openLabel="open"
    >
      <MultiSelectInput.Option value="en_US">English</MultiSelectInput.Option>
      <MultiSelectInput.Option value="fr_FR">French</MultiSelectInput.Option>
      <MultiSelectInput.Option value="de_DE">German</MultiSelectInput.Option>
      <MultiSelectInput.Option value="es_ES">Spanish</MultiSelectInput.Option>
    </MultiSelectInput>
  );

  const input = screen.getByRole('textbox');
  userEvent.type(input, '{backspace}{backspace}');

  expect(onChange).toBeCalledWith(['en_US']);
});

test('it does not remove the chip when the search value is not empty', () => {
  const onChange = jest.fn();
  render(
    <MultiSelectInput
      value={['en_US', 'fr_FR']}
      onChange={onChange}
      placeholder="Placeholder"
      removeLabel="Remove"
      emptyResultLabel="Empty result"
      openLabel="open"
    >
      <MultiSelectInput.Option value="en_US">English</MultiSelectInput.Option>
      <MultiSelectInput.Option value="fr_FR">French</MultiSelectInput.Option>
      <MultiSelectInput.Option value="de_DE">German</MultiSelectInput.Option>
      <MultiSelectInput.Option value="es_ES">Spanish</MultiSelectInput.Option>
    </MultiSelectInput>
  );

  const input = screen.getByRole('textbox');
  userEvent.type(input, 'something{backspace}{backspace}');

  expect(screen.getByDisplayValue('somethi')).toBeInTheDocument();
  expect(onChange).not.toBeCalled();
});

test('MultiSelectInput supports ...rest props', () => {
  const onChange = jest.fn();
  render(
    <MultiSelectInput
      value={[]}
      data-testid="my_value"
      removeLabel="Remove"
      emptyResultLabel="Empty result"
      onChange={onChange}
    />
  );
  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});

test('MultiSelectInput does not support duplicated options', () => {
  const mockConsole = jest.spyOn(console, 'error').mockImplementation();
  expect(() => {
    const onChange = jest.fn();
    render(
      <MultiSelectInput value={['en_US']} onChange={onChange} removeLabel="Remove" emptyResultLabel="Empty result">
        <MultiSelectInput.Option value="en_US">English</MultiSelectInput.Option>
        <MultiSelectInput.Option value="en_US">French</MultiSelectInput.Option>
      </MultiSelectInput>
    );
  }).toThrowError('Duplicate option value en_US');
  mockConsole.mockRestore();
});

test('It throws when passing non string children', () => {
  const mockConsole = jest.spyOn(console, 'error').mockImplementation();
  expect(() => {
    const onChange = jest.fn();
    render(
      <MultiSelectInput value={['en_US']} onChange={onChange} removeLabel="Remove" emptyResultLabel="Empty result">
        <MultiSelectInput.Option value="en_US">
          {/* @ts-expect-error only accepts string */}
          <span />
        </MultiSelectInput.Option>
      </MultiSelectInput>
    );
  }).toThrowError('Multi select only accepts string as Option');
  mockConsole.mockRestore();
});
