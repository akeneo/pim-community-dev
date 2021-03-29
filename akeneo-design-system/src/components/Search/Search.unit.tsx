import React from 'react';
import {render, screen} from '../../storybook/test-util';
import {Search} from './Search';
import {SwitcherButton} from '../SwitcherButton/SwitcherButton';
import userEvent from '@testing-library/user-event';

test('It renders the Search component with its children', () => {
  const onSearchChange = jest.fn();
  render(
    <Search onSearchChange={onSearchChange} searchValue="" title="My title" placeholder="My placeholder">
      <Search.ResultCount>45 results</Search.ResultCount>
      <SwitcherButton label={'Label'}>Value</SwitcherButton>
    </Search>
  );

  expect(screen.getByTitle('My title')).toBeInTheDocument();
  expect(screen.getByText('45 results')).toBeInTheDocument();
  expect(screen.getByText('Value')).toBeInTheDocument();
});

test('It calls the onSearchChange callback when the value is changed', () => {
  const onSearchChange = jest.fn();
  render(<Search onSearchChange={onSearchChange} searchValue="" title="Search" />);

  userEvent.paste(screen.getByTitle('Search'), 'hey!');

  expect(onSearchChange).toBeCalledWith('hey!');
});
