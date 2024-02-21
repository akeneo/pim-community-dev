import React from 'react';
import {render, screen, fireEvent} from '../../tests/test-utils';
import {CategoriesSelector} from '../CategoriesSelector';

jest.mock('../../hooks/useCategoryTrees');
jest.mock('../../hooks/useCategoryTree');
jest.mock('../../hooks/useCategoryLabels');

describe('CategoriesSelector', () => {
  it('should render the categories selector and delete item', async () => {
    const onChange = jest.fn();
    render(<CategoriesSelector categoryCodes={['category1', 'category2']} onChange={onChange} />);

    expect(await screen.findByText('Category 1')).toBeInTheDocument();
    expect(screen.getByText('[category2]')).toBeInTheDocument();
    fireEvent.click(screen.getByTestId('remove-0'));
    expect(onChange).toBeCalledWith(['category2']);
  });
});
