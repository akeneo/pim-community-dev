import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {screen} from '@testing-library/react';
import ProposalDiffTable from '../../../src/proposals/ProposalDiffTable';

jest.mock('../../../src/fetchers/AttributeFetcher');
jest.mock('../../../src/fetchers/SelectOptionsFetcher');

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
});
