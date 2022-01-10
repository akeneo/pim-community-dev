import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {screen} from '@testing-library/react';
import {RowSelector} from '../../../src';
import {TestAttributeContextProvider} from '../../shared/TestAttributeContextProvider';
import {getComplexTableAttribute} from '../../factories';

jest.mock('../../../src/fetchers/SelectOptionsFetcher');
jest.mock('../../../src/fetchers/RecordFetcher');

describe('RowSelector', () => {
  it('should render component with select column type', async () => {
    renderWithProviders(
      <TestAttributeContextProvider attribute={getComplexTableAttribute()}>
        <RowSelector value={'pepper'} onChange={jest.fn()} />
      </TestAttributeContextProvider>
    );

    expect(await screen.findByText('Pepper')).toBeInTheDocument();
  });

  it('should render component with record column type', async () => {
    renderWithProviders(
      <TestAttributeContextProvider attribute={getComplexTableAttribute('reference_entity')}>
        <RowSelector value={'lannion00893335_2e73_41e3_ac34_763fb6a35107'} onChange={jest.fn()} />
      </TestAttributeContextProvider>
    );

    expect(await screen.findByText('Lannion')).toBeInTheDocument();
  });
});
