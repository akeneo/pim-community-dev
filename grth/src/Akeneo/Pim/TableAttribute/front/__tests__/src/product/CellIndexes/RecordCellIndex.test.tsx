import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {screen} from '@testing-library/react';
import {defaultCellMatchersMapping, getComplexTableAttribute} from '../../../factories';
import {RecordCellIndex} from '../../../../src/product/CellIndexes';
import {TestAttributeContextProvider} from '../../../shared/TestAttributeContextProvider';

jest.mock('../../../../src/fetchers/RecordFetcher');

describe('RecordCellIndex', () => {
  it('should render code if record is not found', async () => {
    renderWithProviders(
      <TestAttributeContextProvider attribute={getComplexTableAttribute('record')}>
        <RecordCellIndex cellMatchersMapping={defaultCellMatchersMapping} searchText={''} value={'unknown_record'} />
      </TestAttributeContextProvider>
    );

    expect(await screen.findByText('[unknown_record]')).toBeInTheDocument();
  });
});
