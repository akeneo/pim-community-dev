import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import EmptyFilterValue from '../../../../src/datagrid/FilterValues/EmptyFilterValue';

describe('EmptyFilterValue', () => {
  it('should display nothing', () => {
    const r = renderWithProviders(<EmptyFilterValue value={true} onChange={jest.fn()} columnCode={'is_allergenic'} />);

    expect(r.container.innerHTML).toEqual('');
  });
});
