import React from 'react';
import {render, screen} from '../../tests/test-utils';
import {TabValidationErrors} from '../TabValidationErrors';

describe('TabValidationErrors', () => {
  it('should display unique error', () => {
    const errors = [{path: 'path[0].string', message: 'error on item'}];
    render(<TabValidationErrors errors={errors} />);

    expect(screen.getByRole('list')).toBeInTheDocument();
    expect(screen.getByText('error on item')).toBeInTheDocument();
  });
  it('should display list of errors', () => {
    const errors = [
      {path: 'path[0].string', message: 'error on item'},
      {path: 'path[1].string', message: 'error on another item'},
    ];
    render(<TabValidationErrors errors={errors} />);

    expect(screen.getAllByRole('list')).toHaveLength(1);
    expect(screen.getAllByRole('listitem')).toHaveLength(2);
    expect(screen.getByText('error on item')).toBeInTheDocument();
    expect(screen.getByText('error on another item')).toBeInTheDocument();
  });
});
