import React from 'react';
import {createEvent, fireEvent, render, screen} from '@testing-library/react';
import '@testing-library/jest-dom/extend-expect';
import {pimTheme} from 'akeneo-design-system';
import {ThemeProvider} from 'styled-components';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {CategoryTree, CategoryTreeModel, CategoryTreeProvider} from '../../../../../../src';
import {aCategoryTree} from '../../../../utils/provideCategoryHelper';

const root: CategoryTreeModel = aCategoryTree('a_root', ['cat_1', 'cat_2', 'cat_3'], true, false, 1234);

describe('CategoryTree', () => {
  test('it renders a category tree', () => {
    renderCategoryTree(root);
    expect(screen.getByText('[a_root]')).toBeInTheDocument();
    expect(screen.getByText('[cat_1]')).toBeInTheDocument();
    expect(screen.getByText('[cat_2]')).toBeInTheDocument();
    expect(screen.getByText('[cat_3]')).toBeInTheDocument();
    expect(root.children?.map(child => child.code)).toStrictEqual(['cat_1', 'cat_2', 'cat_3']);

    const firstChildDragHandler = screen.getAllByTestId('drag-initiator')[0];

    const dragStartEvent = createEvent.dragStart(firstChildDragHandler);
    const setDragImage = jest.fn();
    Object.assign(dragStartEvent, {
      dataTransfer: {
        setDragImage,
      },
    });
    fireEvent(firstChildDragHandler, dragStartEvent);

    // const dragStartEvent = createEvent.dragStart(firstChildDragHandler);
    // fireEvent.mouseDown(screen.getAllByTestId('drag-initiator')[0]);

    fireEvent.dragStart(screen.getByTestId('[cat_1]'));
    fireEvent.dragOver(screen.getByTestId('[cat_3]'));
    fireEvent.drop(screen.getByTestId('[cat_3]'));
    fireEvent.dragEnd(screen.getByTestId('[cat_3]'));
    // console.log(screen.getAllByRole('treeitem')[1].outerHTML);

    expect(root.children?.map(child => child.code)).toStrictEqual(['cat_2', 'cat_3', 'cat_1']);
  });

  // test('it can be reordered', () => {
  //
  // })
});

const renderCategoryTree = (root: CategoryTreeModel) => {
  return render(<DependenciesProvider>
    <ThemeProvider theme={pimTheme}>
      <CategoryTreeProvider root={root}>
        <CategoryTree sortable={true} root={root} followCategory={jest.fn()} addCategory={jest.fn()}
                      deleteCategory={jest.fn()}/>
      </CategoryTreeProvider>
    </ThemeProvider>
  </DependenciesProvider>);
};
