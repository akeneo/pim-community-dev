import React from 'react';
import {fireEvent, render, screen} from '../../storybook/test-util';
import {Block} from './Block';
import {PlusIcon} from '../../icons';

test('it calls onRemove handler when user clicks on remove icon button', () => {
  const onRemove = jest.fn();
  render(
    <Block removable={true} onRemove={onRemove} removeLabel={'Remove me, I am a bad block'}>
      My block
    </Block>
  );

  const removeIconButton = screen.getByTitle('Remove me, I am a bad block');
  fireEvent.click(removeIconButton);

  expect(onRemove).toBeCalled();
});

test('Block supports forwardRef', () => {
  const ref = {current: null};

  render(<Block ref={ref}>My block</Block>);

  expect(ref.current).not.toBe(null);
});

test('Block supports ...rest props', () => {
  render(<Block data-testid="my_value">My block</Block>);

  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});

test('it renders children with icon', () => {
  render(
    <Block>
      <PlusIcon data-testid="children-icon" /> My block
    </Block>
  );

  expect(screen.getByText('My block')).toBeInTheDocument();
  expect(screen.getByTestId('children-icon')).toBeInTheDocument();
});
