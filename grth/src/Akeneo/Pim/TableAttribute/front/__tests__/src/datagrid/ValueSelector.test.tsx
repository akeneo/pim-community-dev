import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {ValueSelector} from '../../../src/datagrid';
import {defaultFilterValuesMapping, getComplexTableAttribute} from '../../factories';

describe('ValueSelector', () => {
  it('should display empty value selector', () => {
    const r = renderWithProviders(
      <ValueSelector
        dataType={'text'}
        operator={'EMPTY'}
        onChange={jest.fn()}
        columnCode={'part'}
        attribute={getComplexTableAttribute()}
        filterValuesMapping={defaultFilterValuesMapping}
      />
    );

    expect(r.container.innerHTML).toEqual('');
  });
});
