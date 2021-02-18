import React from 'react';
import {SelectInput} from './SelectInput';
import {Locale} from '../../../components';
import {render, screen, fireEvent} from '../../../storybook/test-util';

test('it renders its children properly', () => {
  const onChange = jest.fn();
  render(
    <SelectInput value="en_US" onChange={onChange} placeholder="Placeholder" emptyResultLabel="Empty result">
      <SelectInput.Option value="en_US" title="English (United States)">
        <Locale code="en_US" languageLabel="English" />
      </SelectInput.Option>
      <SelectInput.Option value="fr_FR" title="French (France)">
        <Locale code="fr_FR" languageLabel="French" />
      </SelectInput.Option>
      <SelectInput.Option value="de_DE" title="German (Germany)">
        <Locale code="de_DE" languageLabel="German" />
      </SelectInput.Option>
      <SelectInput.Option value="es_ES" title="Spanish (Spain)">
        <Locale code="es_ES" languageLabel="Spanish" />
      </SelectInput.Option>
    </SelectInput>
  );

  const input = screen.getByRole('textbox');
  fireEvent.click(input);

  expect(screen.queryByText('German')).toBeInTheDocument();

  fireEvent.click(screen.getByRole('textbox'));
  expect(screen.queryByText('German')).not.toBeInTheDocument();

  fireEvent.click(screen.getByRole('textbox'));
  expect(screen.queryByText('German')).toBeInTheDocument();

  const germanOption = screen.getByText('German');
  expect(germanOption).toBeInTheDocument();
  fireEvent.click(germanOption);
  expect(onChange).toHaveBeenCalledWith('de_DE');
});

test('it handles search', () => {
  const onChange = jest.fn();
  render(
    <SelectInput value="en_US" onChange={onChange} placeholder="Placeholder" emptyResultLabel="Empty result">
      <SelectInput.Option value="en_US" title="English (United States)">
        <Locale code="en_US" languageLabel="English" />
      </SelectInput.Option>
      <SelectInput.Option value="fr_FR" title="French (France)">
        Français
      </SelectInput.Option>
      <SelectInput.Option value="de_DE">
        <Locale code="de_DE" languageLabel="German" />
      </SelectInput.Option>
      <SelectInput.Option value="es_ES" title="Spanish (Spain)">
        <Locale code="es_ES" languageLabel="Spanish" />
      </SelectInput.Option>
    </SelectInput>
  );

  const input = screen.getByRole('textbox');
  fireEvent.click(input);
  fireEvent.change(input, {target: {value: 'Français'}});

  const germanOption = screen.queryByText('German');
  expect(germanOption).not.toBeInTheDocument();
  const frenchOption = screen.getByText('Français');
  expect(frenchOption).toBeInTheDocument();
  fireEvent.keyDown(input, {key: 'Enter', code: 'Enter'});
  expect(onChange).toHaveBeenCalledWith('fr_FR');

  fireEvent.click(input);
  fireEvent.change(input, {target: {value: 'Spain'}});

  const spainOption = screen.getByText('Spanish');
  expect(spainOption).toBeInTheDocument();
  fireEvent.keyDown(input, {key: 'Enter', code: 'Enter'});
  expect(onChange).toHaveBeenCalledWith('es_ES');
});

test('it handles empty cases', () => {
  const onChange = jest.fn();
  render(
    <SelectInput value={null} onChange={onChange} placeholder="Placeholder" emptyResultLabel="Empty result">
      <SelectInput.Option value="en_US" title="English (United States)">
        <Locale code="en_US" languageLabel="English" />
      </SelectInput.Option>
      <SelectInput.Option value="fr_FR" title="French (France)">
        <Locale code="fr_FR" languageLabel="French" />
      </SelectInput.Option>
      <SelectInput.Option value="de_DE" title="German (Germany)">
        <Locale code="de_DE" languageLabel="German" />
      </SelectInput.Option>
      <SelectInput.Option value="es_ES" title="Spanish (Spain)">
        <Locale code="es_ES" languageLabel="Spanish" />
      </SelectInput.Option>
    </SelectInput>
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

test('it handles clearing the field', () => {
  const onChange = jest.fn();
  render(
    <SelectInput
      value="en_US"
      onChange={onChange}
      placeholder="Placeholder"
      emptyResultLabel="Empty result"
      clearSelectLabel="clear"
    >
      <SelectInput.Option value="en_US" title="English (United States)">
        <Locale code="en_US" languageLabel="English" />
      </SelectInput.Option>
      <SelectInput.Option value="fr_FR" title="French (France)">
        <Locale code="fr_FR" languageLabel="French" />
      </SelectInput.Option>
      <SelectInput.Option value="de_DE" title="German (Germany)">
        <Locale code="de_DE" languageLabel="German" />
      </SelectInput.Option>
      <SelectInput.Option value="es_ES" title="Spanish (Spain)">
        <Locale code="es_ES" languageLabel="Spanish" />
      </SelectInput.Option>
    </SelectInput>
  );

  const clearButton = screen.getByTitle('clear');
  fireEvent.click(clearButton);

  expect(onChange).toHaveBeenCalledWith(null);
});

test('it handles keyboard events', () => {
  const onChange = jest.fn();
  render(
    <SelectInput
      value="en_US"
      onChange={onChange}
      placeholder="Placeholder"
      emptyResultLabel="Empty result"
      openSelectLabel="open"
      clearSelectLabel="clear"
    >
      <SelectInput.Option value="en_US" title="English (United States)">
        <Locale code="en_US" languageLabel="English" />
      </SelectInput.Option>
      <SelectInput.Option value="fr_FR" title="French (France)">
        <Locale code="fr_FR" languageLabel="French" />
      </SelectInput.Option>
      <SelectInput.Option value="de_DE" title="German (Germany)">
        <Locale code="de_DE" languageLabel="German" />
      </SelectInput.Option>
      <SelectInput.Option value="es_ES" title="Spanish (Spain)">
        <Locale code="es_ES" languageLabel="Spanish" />
      </SelectInput.Option>
    </SelectInput>
  );

  const clearButton = screen.getByTitle('clear');
  fireEvent.keyDown(clearButton, {key: 'Enter', code: 'Enter'});

  expect(onChange).toHaveBeenCalledWith(null);

  const openButton = screen.getByTitle('open');
  fireEvent.keyDown(openButton, {key: 'Enter', code: 'Enter'});

  const germanOption = screen.queryByText('German');
  expect(germanOption).toBeInTheDocument();
});

test('SelectInput supports ...rest props', () => {
  const onChange = jest.fn();
  render(<SelectInput value="noice" data-testid="my_value" emptyResultLabel="Empty result" onChange={onChange} />);
  expect(screen.getByRole('textbox')).toBeInTheDocument();
});

test('SelectInput does not support duplicated options', () => {
  const mockConsole = jest.spyOn(console, 'error').mockImplementation();
  expect(() => {
    const onChange = jest.fn();
    render(
      <SelectInput value="en_US" onChange={onChange} emptyResultLabel="Empty result">
        <SelectInput.Option value="en_US" title="English (United States)">
          <Locale code="en_US" languageLabel="English" />
        </SelectInput.Option>
        <SelectInput.Option value="en_US" title="French (France)">
          <Locale code="fr_FR" languageLabel="French" />
        </SelectInput.Option>
      </SelectInput>
    );
  }).toThrowError('Duplicate option value en_US');
  mockConsole.mockRestore();
});
