import React from 'react';
import {fireEvent, render, screen} from '../../storybook/test-util';
import {Block} from './Block';

test('it calls onRemove handler when user clicks on remove icon', () => {
  const onRemove = jest.fn();
  render(
    <Block removable={true} onRemove={onRemove}>
      My block
    </Block>
  );

  const block = screen.getByText('My block');
  fireEvent.click(block);

  expect(onRemove).toBeCalled();
});

test('it displays an anchor when providing a `href`', () => {
  render(<Block href="https://akeneo.com/">Hello</Block>);

  expect(screen.getByText('Hello').closest('a')).toHaveAttribute('href', 'https://akeneo.com/');
});

test('Block supports forwardRef', () => {
  const ref = {current: null};

  render(
    <Block ref={ref}>
      My block
    </Block>
  );

  expect(ref.current).not.toBe(null);
});

test('Block supports ...rest props', () => {
  render(
    <Block data-testid="my_value">
      My block
    </Block>
  );

  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});
