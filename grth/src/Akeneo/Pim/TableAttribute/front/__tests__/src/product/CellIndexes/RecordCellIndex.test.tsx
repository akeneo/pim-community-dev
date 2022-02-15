import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {screen} from '@testing-library/react';
import {getComplexTableAttribute} from '../../../factories';
import {RecordCellIndex} from '../../../../src/product/CellIndexes';
import {TestAttributeContextProvider} from '../../../shared/TestAttributeContextProvider';
import {pimTheme} from 'akeneo-design-system';

jest.mock('../../../../src/fetchers/RecordFetcher');

describe('RecordCellIndex', () => {
  it('should render code if record is not found', async () => {
    renderWithProviders(
      <TestAttributeContextProvider attribute={getComplexTableAttribute('reference_entity')}>
        <RecordCellIndex searchText={''} value={'unknown_record'} />
      </TestAttributeContextProvider>
    );

    expect(await screen.findByText('[unknown_record]')).toBeInTheDocument();
  });

  it('should match record cell if there is cellMappings', async () => {
    renderWithProviders(
      <TestAttributeContextProvider attribute={getComplexTableAttribute('reference_entity')}>
        <RecordCellIndex searchText={'Vannes'} value={'vannes00bcf56a_2aa9_47c5_ac90_a973460b18a3'} />
      </TestAttributeContextProvider>
    );

    const cellContent = await screen.findByText('Vannes');
    expect(cellContent).toHaveStyle({background: pimTheme.color.green10});
  });
});
