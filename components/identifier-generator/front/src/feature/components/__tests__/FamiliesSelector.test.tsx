import React from 'react';
import {FamiliesSelector} from '../';
import {render, screen} from '../../tests/test-utils';

describe('FamiliesSelector', () => {
  it('should render the family labels from family codes', async () => {
    render(<FamiliesSelector onChange={jest.fn()} familyCodes={['Family1', 'Family2']} />);

    expect(await screen.findByText('Family1 label')).toBeInTheDocument();
  });

  it('should render unauthorized error', async () => {
    render(<FamiliesSelector onChange={jest.fn()} familyCodes={['unauthorized']} />);

    expect(await screen.findByText('pim_error.unauthorized_list_families')).toBeInTheDocument();
  });

  it('should render default error', async () => {
    render(<FamiliesSelector onChange={jest.fn()} familyCodes={['unknown']} />);

    expect(await screen.findByText('pim_error.general')).toBeInTheDocument();
  });
});
