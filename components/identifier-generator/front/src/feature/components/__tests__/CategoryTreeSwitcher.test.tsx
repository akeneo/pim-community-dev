import React from 'react';
import {render, screen} from '../../tests/test-utils';
import {CategoryTreeSwitcher} from '../CategoryTreeSwitcher';
import {fireEvent} from '@testing-library/react';
jest.mock('../../hooks/useCategoryTrees');

describe('CategoryTreeSwitcher', () => {
  it('should render the tree switcher', async () => {
    const onChange = jest.fn();
    render(<CategoryTreeSwitcher onChange={onChange} value={'masterCatalog'} />);

    expect(await screen.findByText('pim_identifier_generator.selection.settings.categories.tree:')).toBeInTheDocument();
    expect(await screen.findByText('Master Catalog')).toBeInTheDocument();

    fireEvent.click(screen.getByRole('button'));
    expect(screen.getByText('Print')).toBeInTheDocument();
    fireEvent.click(screen.getByText('Print'));
    expect(onChange).toBeCalledWith({
      code: 'print',
      id: 69,
      label: 'Print',
      selected: true,
    });
  });
});
