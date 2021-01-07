import React from 'react';
import {Pagination} from './Pagination';
import {fireEvent, render, screen} from '../../storybook/test-util';

test('it renders nothing if there is no item', () => {
  render(<Pagination currentPage={1} itemsTotal={0} onClick={jest.fn()} />);

  expect(screen.queryAllByTestId('paginationItem')).toHaveLength(0);
});

test('it renders nothing if number of items are less than number of items per page', () => {
  render(<Pagination currentPage={1} itemsTotal={24} itemsPerPage={25} onClick={jest.fn()} />);

  expect(screen.queryAllByTestId('paginationItem')).toHaveLength(0);
});

test('it renders a pagination of two pages', () => {
  const result = render(<Pagination currentPage={1} itemsTotal={50} itemsPerPage={25} onClick={jest.fn()} />);
  expect(result.container.textContent).toBe(expectedPagination(['1', '2']));
});

test('it renders all pagination items where there are 4 or less items', () => {
  const result = render(<Pagination currentPage={1} itemsTotal={8} itemsPerPage={2} onClick={jest.fn()} />);
  expect(result.container.textContent).toBe(expectedPagination(['1', '2', '3', '4']));

  result.rerender(<Pagination currentPage={1} itemsTotal={9} itemsPerPage={2} onClick={jest.fn()} />);
  expect(result.container.textContent).toBe(expectedPagination(['1', '2', '3', '...', '5']));
});

test('it renders the 2 first pagination items and the last page if the current page is the first one', () => {
  const result = render(<Pagination currentPage={1} itemsTotal={50} itemsPerPage={2} onClick={jest.fn()} />);
  expect(result.container.textContent).toBe(expectedPagination(['1', '2', '3', '...', '25']));
});

test('it renders the 3 first pagination item and the last page if the current page is the second one', () => {
  const result = render(<Pagination currentPage={2} itemsTotal={50} itemsPerPage={2} onClick={jest.fn()} />);
  expect(result.container.textContent).toBe(expectedPagination(['1', '2', '3', '...', '25']));
});

test('it renders the 4 first pagination item and the last page if the current page is the third one', () => {
  const result = render(<Pagination currentPage={3} itemsTotal={50} itemsPerPage={2} onClick={jest.fn()} />);
  expect(result.container.textContent).toBe(expectedPagination(['1', '2', '3', '4', '...', '25']));
});

test('it renders the 3 last pagination item and the first page if the last page is the current page', () => {
  const result = render(<Pagination currentPage={25} itemsTotal={50} itemsPerPage={2} onClick={jest.fn()} />);
  expect(result.container.textContent).toBe(expectedPagination(['1', '...', '23', '24', '25']));
});

test('it renders the 3 last pagination item and the first page if the current page is the second last page', () => {
  const result = render(<Pagination currentPage={24} itemsTotal={50} itemsPerPage={2} onClick={jest.fn()} />);
  expect(result.container.textContent).toBe(expectedPagination(['1', '...', '23', '24', '25']));
});

test('it renders the 4 last pagination item and the first page if the current page is the third last page', () => {
  const result = render(<Pagination currentPage={23} itemsTotal={50} itemsPerPage={2} onClick={jest.fn()} />);
  expect(result.container.textContent).toBe(expectedPagination(['1', '...', '22', '23', '24', '25']));
});

test('it renders the first page, the last page and the previous and next page of any current page', () => {
  const result = render(<Pagination currentPage={12} itemsTotal={50} itemsPerPage={2} onClick={jest.fn()} />);
  expect(result.container.textContent).toBe(expectedPagination(['1', '...', '11', '12', '13', '...', '25']));
});

test('it ensures pagination item is clickable', () => {
  const onClickPage = jest.fn();

  render(<Pagination currentPage={12} itemsTotal={50} itemsPerPage={2} onClick={onClickPage} />);

  const button = screen.getByTitle('No. 12');
  fireEvent.click(button);
  expect(onClickPage).toBeCalled();
});

test('it ensures separator click does nothing', () => {
  const onClickSeparator = jest.fn();

  const result = render(<Pagination currentPage={12} itemsTotal={50} itemsPerPage={2} onClick={onClickSeparator} />);

  expect(result.container.textContent).toBe(expectedPagination(['1', '...', '11', '12', '13', '...', '25']));

  const separator = screen.getAllByTestId('paginationItem')[1];
  fireEvent.click(separator);
  expect(onClickSeparator).not.toBeCalled();
});

test('it renders nothing if current page is out of bound', () => {
  render(<Pagination currentPage={1200} itemsTotal={50} itemsPerPage={25} onClick={jest.fn()} />);
  expect(screen.queryAllByTestId('paginationItem')).toHaveLength(0);
});

test('it renders nothing if items per page prop is invalid', () => {
  render(<Pagination currentPage={1} itemsTotal={50} itemsPerPage={0} onClick={jest.fn()} />);
  expect(screen.queryAllByTestId('paginationItem')).toHaveLength(0);
});

function expectedPagination(expectedPagination: string[]) {
  expect(screen.getAllByTestId('paginationItem')).toHaveLength(expectedPagination.length);
  return expectedPagination.join('');
}
