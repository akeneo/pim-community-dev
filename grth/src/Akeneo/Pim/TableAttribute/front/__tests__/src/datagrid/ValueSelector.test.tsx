import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {ValueSelector} from '../../../src';

describe('ValueSelector', () => {
  it('should display empty value selector', () => {
    const r = renderWithProviders(<ValueSelector operator={'EMPTY'} onChange={jest.fn()} columnCode={'part'} />);

    expect(r.container.innerHTML).toEqual('');
  });
});
