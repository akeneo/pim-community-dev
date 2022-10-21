import React from 'react';
import {render, screen} from '../../tests/test-utils';
import {IdentifierGenerator} from '../../models';
import {EditGeneratorPage} from '../';

const initialGenerator: IdentifierGenerator = {
  code: 'initialCode',
  labels: {
    en_US: 'Initial Label',
  },
  conditions: [],
  structure: [],
  delimiter: null,
  target: 'sku'
};

describe('EditGeneratorPage', () => {
  it('should render page', () => {
    render(<EditGeneratorPage initialGenerator={initialGenerator}/>);
    expect(screen.getByText('pim_common.save')).toBeInTheDocument();
  });
});
