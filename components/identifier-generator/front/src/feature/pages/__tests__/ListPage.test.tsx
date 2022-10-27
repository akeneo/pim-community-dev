import React from 'react';
import {render, screen} from '../../tests/test-utils';
import {ListPage} from '../ListPage';

describe('ListPage', () => {
  it('should display an informative message when there are no generators yet', () => {
    render(<ListPage onCreate={jest.fn()} isCreateEnabled={true} />);

    expect(screen.getByText('pim_identifier_generator.list.first_generator')).toBeVisible();
    expect(screen.getByText('pim_identifier_generator.list.identifier')).not.toBeVisible();
  });
  
  it('should display the generators list', () => {
    render(<ListPage onCreate={jest.fn()} isCreateEnabled={true})
  })
});
