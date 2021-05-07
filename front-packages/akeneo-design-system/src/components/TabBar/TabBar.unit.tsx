import React from 'react';
import {TabBar} from './TabBar';
import {render, screen} from '../../storybook/test-util';
import userEvent from '@testing-library/user-event';

test('it renders its children properly', () => {
  render(
    <TabBar>
      <TabBar.Tab isActive={false}>First tab</TabBar.Tab>
      <TabBar.Tab isActive={false}>Another tab</TabBar.Tab>
      <TabBar.Tab isActive={false}>Last tab</TabBar.Tab>
    </TabBar>
  );

  expect(screen.getByText('First tab')).toBeInTheDocument();
  expect(screen.getByText('Another tab')).toBeInTheDocument();
  expect(screen.getByText('Last tab')).toBeInTheDocument();
});

test('TabBar supports ...rest props', () => {
  render(<TabBar data-testid="my_value" />);
  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});

test('it renders its children properly', () => {
  const handleClick = jest.fn();

  render(
    <TabBar>
      <TabBar.Tab isActive={false} onClick={handleClick}>
        First tab
      </TabBar.Tab>
      <TabBar.Tab isActive={false}>Another tab</TabBar.Tab>
      <TabBar.Tab isActive={false}>Last tab</TabBar.Tab>
    </TabBar>
  );

  userEvent.click(screen.getByText('First tab'));
  expect(handleClick).toBeCalled();
});
