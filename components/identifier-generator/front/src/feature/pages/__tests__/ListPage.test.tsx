import React from 'react';
import {fireEvent, render, screen, waitFor} from '../../tests/test-utils';
import {ListPage} from '../ListPage';
import {IdentifierGenerator, PROPERTY_NAMES} from '../../models';
import {useGetGenerators} from '../../hooks/useGetGenerators';
import {mocked} from 'ts-jest/utils';
import {fireEvent} from '@testing-library/react';

jest.mock('../../hooks/useGetGenerators', () => ({
  useGetGenerators: jest.fn(),
}));

const mockHistoryPush = jest.fn();

jest.mock('react-router-dom', () => ({
  ...jest.requireActual('react-router-dom'),
  useHistory: () => ({
    push: mockHistoryPush,
  }),
}));

const mockedList: IdentifierGenerator[] = [
  {
    code: 'test',
    conditions: [],
    structure: [{type: PROPERTY_NAMES.FREE_TEXT, string: 'AKN'}],
    labels: {ca_ES: 'azeaze', en_US: 'Sku generator'},
    target: 'sku',
    delimiter: null,
  },
];

describe('ListPage', () => {
  it('should display an informative message when there are no generators yet', () => {
    mocked(useGetGenerators).mockReturnValue({
      data: [],
      isLoading: false,
      refetch: jest.fn(),
    });

    render(<ListPage onCreate={jest.fn()} />);

    expect(screen.getByText('pim_identifier_generator.list.first_generator')).toBeVisible();
    expect(screen.queryByText('pim_identifier_generator.list.create_info')).not.toBeInTheDocument();
    expect(screen.getByText('pim_common.create')).toBeEnabled();
  });

  it('should display the generators list', async () => {
    mocked(useGetGenerators).mockReturnValue({
      data: mockedList,
      isLoading: false,
      refetch: jest.fn(),
    });
    render(<ListPage onCreate={jest.fn()} />);

    await waitFor(() => {
      expect(screen.queryByText('pim_identifier_generator.list.create_info')).toBeVisible();
    });
    expect(screen.getByText('pim_common.create')).toBeDisabled();
    expect(screen.queryByText('pim_identifier_generator.list.first_generator')).not.toBeInTheDocument();

    expect(screen.getByText('[test]')).toBeVisible();
  });

  it('should redirect to edit page on list item click', async () => {
    mocked(useGetGenerators).mockReturnValue({
      data: mockedList,
      isLoading: false,
    });

    render(<ListPage onCreate={jest.fn()} />);

    await waitFor(() => {
      expect(screen.queryByText('pim_identifier_generator.list.create_info')).toBeVisible();
    });

    const rows = screen.getAllByRole('row');
    expect(rows.length).toBe(3);

    fireEvent.click(rows[2]);
    await waitFor(() => {
      expect(mockHistoryPush).toHaveBeenCalledTimes(1);
      expect(mockHistoryPush).toHaveBeenCalledWith('/test');
    });
  });

  it('should display delete modal', () => {
    mocked(useGetGenerators).mockReturnValue({
      data: mockedList,
      isLoading: false,
      refetch: jest.fn(),
    });
    render(<ListPage onCreate={jest.fn()} />);

    expect(screen.queryByText('pim_identifier_generator.deletion.operations')).toBeNull();
    fireEvent.click(screen.getByText('pim_common.delete'));
    expect(screen.queryByText('pim_identifier_generator.deletion.operations')).toBeVisible();
  });
});
