import React from 'react';
import {render, screen} from '../../tests/test-utils';
import {Structure} from '../Structure';

jest.mock('../structure/AddPropertyButton');

describe('Structure', () => {
  it('should render the structure tab', () => {
    render(<Structure />);
    expect(screen.getByText('pim_identifier_generator.structure.title')).toBeInTheDocument();
    expect(screen.getByText('AddPropertyButtonMock')).toBeInTheDocument();
  });
});
