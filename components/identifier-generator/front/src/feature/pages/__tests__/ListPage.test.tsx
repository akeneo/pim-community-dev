import React from 'react';
import {render, screen, waitFor} from '../../tests/test-utils';
import {ListPage} from '../ListPage';
import {IdentifierGenerator, PROPERTY_NAMES} from '../../models';
import {useGetGenerators} from '../../hooks/useGetGenerators';
import {mocked} from 'ts-jest/utils';

jest.mock('../../hooks/useGetGenerators', () => ({
  useGetGenerators: jest.fn(),
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
    });
    render(<ListPage onCreate={jest.fn()} />);

    await waitFor(() => {
      expect(screen.queryByText('pim_identifier_generator.list.create_info')).toBeVisible();
    });
    expect(screen.getByText('pim_common.create')).toBeDisabled();
    expect(screen.queryByText('pim_identifier_generator.list.first_generator')).not.toBeInTheDocument();

    expect(screen.getByText('[test]')).toBeVisible();
  });
});
