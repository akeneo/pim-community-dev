import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {screen} from '@testing-library/react';
import {TwoColumnsLayout} from '../../../src';

describe('TwoColumnsLayout', () => {
  it('should render the 2 columns', async () => {
    renderWithProviders(<TwoColumnsLayout rightColumn={<div>Right Column</div>}>Left Column</TwoColumnsLayout>);
    expect(await screen.findByText('Right Column')).toBeInTheDocument();
    expect(await screen.findByText('Left Column')).toBeInTheDocument();
  });
});
