import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {screen, fireEvent} from '@testing-library/react';
import {AttributeSelector} from '../../../src/jobs';

jest.mock('../../../src/fetchers/AttributeFetcher');

describe('AttributeSelector', () => {
  it('should render the component', async () => {
    const handleChange = jest.fn();
    renderWithProviders(
      <AttributeSelector
        label={'Attribute'}
        onChange={handleChange}
        errorMessage={null}
        initialValue={'nutrition'}
        types={['pim_catalog_table']}
      />
    );

    expect(screen.getByText('Attribute')).toBeInTheDocument();
    expect(await screen.findByText('Nutrition')).toBeInTheDocument();

    fireEvent.click(screen.getByTitle('pim_common.open'));
    expect(screen.getByText('Packaging')).toBeInTheDocument();

    fireEvent.click(screen.getByText('Packaging'));
    expect(handleChange).toBeCalledWith('packaging');

    fireEvent.click(screen.getByTitle('pim_common.clear'));
    expect(handleChange).toBeCalledWith(null);
  });
});
