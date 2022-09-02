import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {screen} from '@testing-library/react';
import ProposalDiffTable from '../../../src/proposals/ProposalDiffTable';

jest.mock('../../../src/fetchers/AttributeFetcher');
jest.mock('../../../src/fetchers/SelectOptionsFetcher');
jest.mock('../../../src/fetchers/RecordFetcher');

const defaultChange = {
  before: [
    {
      ingredient: 'sugar',
      quantity: 300,
      is_allergenic: false,
      part: '550g',
    },
  ],
  after: [
    {
      ingredient: 'sugar',
      quantity: 200,
      is_allergenic: true,
      part: '680g',
    },
  ],
  attributeCode: 'nutrition',
};

const refEntityChanges = {
  before: [
    {
      city: 'vannes00bcf56a_2aa9_47c5_ac90_a973460b18a3',
      city_column: 'brest00bcf56a_2aa9_47c5_ac90_a973460b18a3',
    },
  ],
  after: [
    {
      city: 'nantes00e3cffd_f60e_4a51_925b_d2952bd947e1',
      city_column: 'coueron00893335_2e73_41e3_ac34_763fb6a35107',
    },
    {
      city: 'coueron00893335_2e73_41e3_ac34_763fb6a35107',
      city_column: 'lannion00893335_2e73_41e3_ac34_763fb6a35107',
    },
  ],
  attributeCode: 'city',
};

const deletedChanges = {
  before: [
    {
      ingredient: 'salt',
      quantity: 1000,
      is_allergenic: true,
      part: 'no change',
    },
  ],
  after: [
    {
      ingredient: 'salt',
    },
  ],
  attributeCode: 'nutrition',
};

describe('ProposalDiffTable', () => {
  it('should render the before component', async () => {
    renderWithProviders(<ProposalDiffTable change={defaultChange} accessor='before' />);

    expect(await screen.findByText('pim_table_attribute.form.product.order')).toBeInTheDocument();
    expect(await screen.findByText('3')).toBeInTheDocument();
    expect(await screen.findByText('00')).toBeInTheDocument();
    expect(await screen.findByText('pim_common.no')).toBeInTheDocument();
    expect(await screen.findByText('55')).toBeInTheDocument();
    expect(await screen.findByText('0g')).toBeInTheDocument();
  });

  it('should render the after component', async () => {
    renderWithProviders(<ProposalDiffTable change={defaultChange} accessor='after' />);

    expect(await screen.findByText('pim_table_attribute.form.product.order')).toBeInTheDocument();
    expect(await screen.findByText('2')).toBeInTheDocument();
    expect(await screen.findByText('00')).toBeInTheDocument();
    expect(await screen.findByText('pim_common.yes')).toBeInTheDocument();
    expect(await screen.findByText('68')).toBeInTheDocument();
    expect(await screen.findByText('0g')).toBeInTheDocument();
  });

  it('should display before deleted change', async () => {
    renderWithProviders(<ProposalDiffTable change={deletedChanges} accessor='before' />);

    expect(await screen.findByText('pim_table_attribute.form.product.order')).toBeInTheDocument();
    expect(await screen.findByText('1000')).toBeInTheDocument();
    expect(await screen.findByText('pim_common.yes')).toBeInTheDocument();
    expect(await screen.findByText('no change')).toBeInTheDocument();
  });

  it('should display after deleted change', async () => {
    renderWithProviders(<ProposalDiffTable change={deletedChanges} accessor='after' />);

    expect(await screen.findByText('pim_table_attribute.form.product.order')).toBeInTheDocument();
  });

  it('should render diff with reference entities as value (before)', async () => {
    renderWithProviders(<ProposalDiffTable change={refEntityChanges} accessor='before' />);

    expect(await screen.findByText('Vannes')).toBeInTheDocument();
    expect(screen.getByText('Brest')).toBeInTheDocument();
  });

  it('should render diff with reference entities as value (after)', async () => {
    renderWithProviders(<ProposalDiffTable change={refEntityChanges} accessor='after' />);

    expect(await screen.findByText('Lannion')).toBeInTheDocument();
    expect(screen.getByText('Nantes')).toBeInTheDocument();
    expect(screen.getAllByText('Coueron')).toHaveLength(2);
  });
});
