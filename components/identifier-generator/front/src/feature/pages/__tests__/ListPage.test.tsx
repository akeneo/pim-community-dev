import React from 'react';
import {fireEvent, mockACLs, render, screen, waitFor} from '../../tests/test-utils';
import {ListPage} from '../ListPage';
import {IdentifierGenerator, PROPERTY_NAMES, TEXT_TRANSFORMATION} from '../../models';
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
    text_transformation: TEXT_TRANSFORMATION.NO,
  },
];

const mockedFullList: IdentifierGenerator[] = [];
for (let i = 0; i < 20; i++) {
  mockedFullList[i] = {...mockedList[0]};
  mockedFullList[i].code = `test-${i}`;
  mockedFullList[i].labels = {ca_ES: 'azeaze', en_US: `Sku generator ${i}`};
}

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
    expect(screen.queryByText('pim_identifier_generator.list.max_generator.title')).not.toBeInTheDocument();
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

    expect(screen.getByText('pim_common.create')).toBeVisible();
    expect(screen.queryByText('pim_identifier_generator.list.first_generator')).not.toBeInTheDocument();

    expect(screen.getByText('Sku generator')).toBeVisible();
    expect(await screen.findByText('Sku')).toBeVisible();
  });

  it('should redirect to edit page on list item click', () => {
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

    const rows = screen.getAllByRole('row');
    expect(rows.length).toBe(2);

    fireEvent.click(rows[1]);
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

  it('should not display the create button, or the deletion buttons if ACL is not enabled', async () => {
    mockACLs(true, false);
    mocked(useGetIdentifierGenerators).mockReturnValue({
      data: mockedList,
      isLoading: false,
      refetch: jest.fn(),
      error: null,
    });
    render(<ListPage onCreate={jest.fn()} />);

    await waitFor(() => {
      expect(screen.queryByText('pim_identifier_generator.list.read_only_list')).toBeVisible();
    });

    expect(screen.queryByText('pim_identifier_generator.list.max_generator.title')).not.toBeInTheDocument();
    expect(screen.queryByText('pim_common.create')).toHaveAttribute('disabled');
    expect(screen.queryByText('pim_common.delete')).not.toBeInTheDocument();
    expect(screen.queryByText('pim_common.edit')).not.toBeInTheDocument();
    expect(screen.queryByText('pim_common.view')).toBeInTheDocument();
  });

  it('should display a specific message for users without manage acl if list is empty', () => {
    mockACLs(true, false);
    mocked(useGetIdentifierGenerators).mockReturnValue({
      data: [],
      isLoading: false,
      refetch: jest.fn(),
      error: null,
    });

    render(<ListPage onCreate={jest.fn()} />);

    expect(screen.getByText('pim_identifier_generator.list.read_only_list')).toBeVisible();
    expect(screen.queryByText('pim_identifier_generator.list.max_generator.title')).not.toBeInTheDocument();
    expect(screen.queryByText('pim_identifier_generator.list.first_generator')).not.toBeInTheDocument();
    expect(screen.queryByText('pim_common.create')).toHaveAttribute('disabled');
  });

  it('should display placeholder if the limit is reached', async () => {
    mocked(useGetIdentifierGenerators).mockReturnValue({
      data: mockedFullList,
      isLoading: false,
      refetch: jest.fn(),
      error: null,
    });
    render(<ListPage onCreate={jest.fn()} />);

    const rows = screen.getAllByRole('row');
    expect(rows.length).toBe(21);

    await waitFor(() => {
      expect(screen.queryByText('pim_identifier_generator.list.max_generator.title')).toBeVisible();
    });
    expect(screen.getByText('pim_common.create')).toBeDisabled();
    expect(screen.queryByText('pim_identifier_generator.list.first_generator')).not.toBeInTheDocument();

    expect(screen.getByText('Sku generator 0')).toBeVisible();
    expect(screen.getByText('Sku generator 19')).toBeVisible();
  });
});
