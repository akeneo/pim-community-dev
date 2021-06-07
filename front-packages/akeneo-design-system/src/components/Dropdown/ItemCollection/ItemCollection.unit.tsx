import React from 'react';
import {ItemCollection} from './ItemCollection';
import {render, screen, fireEvent, act} from '../../../storybook/test-util';
import {Item} from '../Item/Item';

type EntryCallback = (entries: {isIntersecting: boolean}[]) => void;

let entryCallback: EntryCallback | undefined = undefined;
const intersectionObserverMock = (callback: EntryCallback) => ({
  observe: jest.fn(() => (entryCallback = callback)),
  unobserve: jest.fn(),
});
window.IntersectionObserver = jest.fn().mockImplementation(intersectionObserverMock);

test('it handles arrow navigation', () => {
  render(
    <ItemCollection>
      <Item>First item</Item>
      <Item>Second item</Item>
      An invalid element
    </ItemCollection>
  );

  expect(screen.getByText('First item').parentNode).toHaveFocus();
  fireEvent.keyDown(screen.getByText('First item'), {key: 'ArrowDown', code: 'ArrowDown'});
  expect(screen.getByText('Second item').parentNode).toHaveFocus();
  fireEvent.keyDown(screen.getByText('Second item'), {key: 'ArrowUp', code: 'ArrowUp'});
  expect(screen.getByText('First item').parentNode).toHaveFocus();
});

test('it calls the next page handler when the last element is almost reached', () => {
  const handleNextPage = jest.fn();

  render(
    <ItemCollection onNextPage={handleNextPage}>
      <Item>First item</Item>
      <Item>Second item</Item>
    </ItemCollection>
  );

  act(() => {
    entryCallback?.([{isIntersecting: true}]);
  });

  expect(handleNextPage).toHaveBeenCalled();
});
