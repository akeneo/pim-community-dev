import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {screen} from '@testing-library/react';
import {RowSelector} from '../../../src';
import {ingredientsSelectOptions} from '../../../src/fetchers/__mocks__/SelectOptionsFetcher';
import {TestAttributeContextProvider} from '../../shared/TestAttributeContextProvider';
import {getComplexTableAttribute} from '../../factories';

jest.mock('../../../src/fetchers/SelectOptionsFetcher');

describe('RowSelector', () => {
  it('should render component with select column type', async () => {
    renderWithProviders(
      <TestAttributeContextProvider attribute={getComplexTableAttribute()}>
        <RowSelector value={ingredientsSelectOptions[1]} onChange={jest.fn()} />
      </TestAttributeContextProvider>
    );

    expect(await screen.findByText('Pepper')).toBeInTheDocument();
  });
});
