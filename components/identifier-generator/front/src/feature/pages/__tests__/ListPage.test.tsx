import React from 'react';
import {fireEvent, render, screen, waitFor} from '../../tests/test-utils';
import {ListPage} from '../ListPage';
import {IdentifierGenerator, PROPERTY_NAMES} from '../../models';
import {useGetIdentifierGenerators} from '../../hooks';
import {mocked} from 'ts-jest/utils';
import {Router} from 'react-router';
import {createMemoryHistory} from 'history';

jest.mock('../DeleteGeneratorModal');
jest.mock('../../hooks/useIdentifierAttributes');
jest.mock('../../hooks/useGetIdentifierGenerators', () => ({
  useGetIdentifierGenerators: jest.fn(),
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
    mocked(useGetIdentifierGenerators).mockReturnValue({
      data: [],
      isLoading: false,
      refetch: jest.fn(),
      error: null,
    });

    render(<ListPage onCreate={jest.fn()} />);

    expect(screen.getByText('pim_identifier_generator.list.first_generator')).toBeVisible();
    expect(screen.queryByText('pim_identifier_generator.list.create_info')).not.toBeInTheDocument();
    expect(screen.getByText('pim_common.create')).toBeEnabled();
  });

  it('should display the generators list', async () => {
    mocked(useGetIdentifierGenerators).mockReturnValue({
      data: mockedList,
      isLoading: false,
      refetch: jest.fn(),
      error: null,
    });
    render(<ListPage onCreate={jest.fn()} />);

    await waitFor(() => {
      expect(screen.queryByText('pim_identifier_generator.list.create_info')).toBeVisible();
    });
    expect(screen.getByText('pim_common.create')).toBeDisabled();
    expect(screen.queryByText('pim_identifier_generator.list.first_generator')).not.toBeInTheDocument();

    expect(screen.getByText('Sku generator')).toBeVisible();
    expect(await screen.findByText('Sku')).toBeVisible();
  });

  it('should redirect to edit page on list item click', async () => {
    mocked(useGetIdentifierGenerators).mockReturnValue({
      data: mockedList,
      isLoading: false,
      refetch: jest.fn(),
      error: null,
    });

    const history = createMemoryHistory();
    render(
      <Router history={history}>
        <ListPage onCreate={jest.fn()} />
      </Router>
    );

    await waitFor(() => {
      expect(screen.queryByText('pim_identifier_generator.list.create_info')).toBeVisible();
    });

    const rows = screen.getAllByRole('row');
    expect(rows.length).toBe(3);

    fireEvent.click(rows[2]);
    expect(history.location.pathname).toEqual('/test');
  });

  it('should delete a generator', () => {
    mocked(useGetIdentifierGenerators).mockReturnValue({
      data: mockedList,
      isLoading: false,
      refetch: jest.fn(),
      error: null,
    });
    render(<ListPage onCreate={jest.fn()} />);

    fireEvent.click(screen.getByText('pim_common.delete'));
    expect(screen.getByText('DeleteGeneratorModalMock')).toBeInTheDocument();
    fireEvent.click(screen.getByText('Delete generator'));
    expect(screen.queryByText('DeleteGeneratorModalMock')).not.toBeInTheDocument();
  });

  it('should cancel deletion of a generator', () => {
    mocked(useGetIdentifierGenerators).mockReturnValue({
      data: mockedList,
      isLoading: false,
      refetch: jest.fn(),
      error: null,
    });
    render(<ListPage onCreate={jest.fn()} />);

    fireEvent.click(screen.getByText('pim_common.delete'));
    expect(screen.getByText('DeleteGeneratorModalMock')).toBeInTheDocument();
    fireEvent.click(screen.getByText('Close modal'));
    expect(screen.queryByText('DeleteGeneratorModalMock')).not.toBeInTheDocument();
  });
});
