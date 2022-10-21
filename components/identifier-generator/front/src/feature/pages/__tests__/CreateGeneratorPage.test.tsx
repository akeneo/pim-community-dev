import React from 'react';
import {render, screen} from '../../tests/test-utils';
import {CreateGeneratorPage} from '../';
import {IdentifierGenerator} from '../../models';

const initialGenerator: IdentifierGenerator = {
  code: 'initialCode',
  labels: {
    en_US: 'Initial Label',
  },
  conditions: [],
  structure: [],
  delimiter: null,
  target: 'sku',
};

describe('CreateGeneratorPage', () => {
  it('should render page', () => {
    render(<CreateGeneratorPage initialGenerator={initialGenerator} />);
    expect(screen.getByText('pim_common.save')).toBeInTheDocument();
  });
});
