import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {act, fireEvent, screen, waitFor} from '@testing-library/react';
import {ImportOptionsButton} from '../../../src/';

jest.mock('../../../src/fetchers/AttributeFetcher');
jest.mock('../../../src/fetchers/AttributeOptionFetcher');

describe('ImportOptionsButton', () => {
  it('should render the component', () => {
    renderWithProviders(<ImportOptionsButton onClick={jest.fn()} />);

    expect(screen.getByText('pim_table_attribute.form.attribute.import_from_existing_attribute')).toBeInTheDocument();
  });

  it('should callback the attribute options', async () => {
    const handleClick = jest.fn();
    renderWithProviders(<ImportOptionsButton onClick={handleClick} />);

    fireEvent.click(screen.getByText('pim_table_attribute.form.attribute.import_from_existing_attribute'));
    expect(await screen.findByText('Simple Select 1')).toBeInTheDocument();
    expect(await screen.findByText('Simple Select 2')).toBeInTheDocument();
    expect(await screen.findAllByText('pim_table_attribute.form.attribute.option_count')).toHaveLength(2);

    act(() => {
      fireEvent.click(screen.getByText('Simple Select 1'));
    });
    expect(screen.queryByText('Simple Select 1')).not.toBeInTheDocument();
    expect(screen.getByTestId('isLoading')).toBeInTheDocument();
    await waitFor(() => {
      expect(screen.queryByTestId('isLoading')).not.toBeInTheDocument();
    });
    expect(handleClick).toBeCalledWith([
      {
        code: 'option_1',
        labels: {en_US: 'Option 1 English'},
      },
      {
        code: 'simple_select_option_2',
        labels: {fr_FR: 'Option 2 French'},
      },
    ]);
  });
});
