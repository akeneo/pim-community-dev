import React from 'react';
import {mockResponse, render, screen} from '../../tests/test-utils';
import {SelectionTab} from '../SelectionTab';

describe('SelectionTab', () => {
  it('should render the selection tab', () => {
    render(<SelectionTab target={'sku'} conditions={[]} />);

    expect(screen.getByText('pim_identifier_generator.tabs.product_selection')).toBeInTheDocument();
  });

  it('should render the default identifier attribute', async () => {
    const expectCall = mockResponse('akeneo_identifier_generator_get_identifier_attributes', 'GET', {
      json: [{code: 'sku', label: 'Sku'}],
    });

    render(<SelectionTab target={'sku'} conditions={[]} />);

    expect(await screen.findByText('Sku')).toBeInTheDocument();

    expectCall();
  });
});
