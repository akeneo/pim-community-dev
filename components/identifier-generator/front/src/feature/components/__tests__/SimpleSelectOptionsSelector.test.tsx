import React from 'react';
import {render} from '../../tests/test-utils';
import {SimpleSelectOptionsSelector} from '../SimpleSelectOptionsSelector';
import {fireEvent, waitFor} from '@testing-library/react';
import {screen} from 'akeneo-design-system/lib/storybook/test-util';

describe('SimpleSelectOptionsSelector', () => {
  it('should show selected values in multi select with any values', async () => {
    const screen = render(
      <SimpleSelectOptionsSelector
        attributeCode={'brand'}
        optionCodes={['option_a', 'invalid_code', 'last_option']}
        onChange={jest.fn()}
      />
    );

    await waitFor(() => {
      expect(screen.getByText('Option A')).toBeInTheDocument();
    });
    expect(screen.getByText('invalid_code')).toBeInTheDocument();
    expect(screen.getByText('[last_option]')).toBeInTheDocument();
  });

  it('should search for options by label and select them', async () => {
    const mockedOnChange = jest.fn();

    const screen = render(
      <SimpleSelectOptionsSelector
        attributeCode={'brand'}
        optionCodes={['option_a', 'invalid_code', 'last_option']}
        onChange={mockedOnChange}
      />
    );

    await waitFor(() => {
      expect(screen.getByText('Option A')).toBeInTheDocument();
    });

    const input = screen.getByRole('textbox');
    fireEvent.click(input);
    fireEvent.change(input, {target: {value: 'Option19'}});

    await waitFor(() => {
      expect(screen.getByText('[Option19]')).toBeInTheDocument();
    });

    const germanOption = screen.queryByText('Option1');
    expect(germanOption).not.toBeInTheDocument();

    fireEvent.click(screen.getByText('[Option19]'));
    expect(mockedOnChange).toHaveBeenCalled();

    expect(screen.getByText('[Option1]')).toBeInTheDocument();
    fireEvent.click(screen.getByText('[Option1]'));
    expect(mockedOnChange).toHaveBeenCalled();
  });
});

it('should render default error', async () => {
  render(
    <SimpleSelectOptionsSelector
      attributeCode={'brand'}
      optionCodes={['error_response']}
      onChange={jest.fn()}
    />
  );

  expect(await screen.findByText('pim_error.general')).toBeInTheDocument();
});
