import React from 'react';
import {fireEvent, render, screen} from '../../storybook/test-util';
import {Block} from './Block';
import {IconButton} from '../IconButton/IconButton';
import {PlusIcon, CloseIcon, ArrowDownIcon} from '../../icons';

test('it renders without actions', () => {
  render(<Block>My block</Block>);

  expect(screen.getByText('My block')).toBeInTheDocument();
});

test('it renders action passed by props', () => {
  const onRemove = jest.fn();
  const onCollapse = jest.fn();

  render(
    <Block
      actions={
        <>
          <IconButton
            level="tertiary"
            ghost="borderless"
            size="small"
            key="collapse"
            icon={<ArrowDownIcon />}
            title="Collapse"
            onClick={onCollapse}
          />
          <IconButton
            level="tertiary"
            ghost="borderless"
            size="small"
            key="remove"
            icon={<CloseIcon />}
            title="Remove"
            onClick={onRemove}
          />
        </>
      }
    >
      My block
    </Block>
  );

  const removeIconButton = screen.getByTitle('Remove');
  fireEvent.click(removeIconButton);
  expect(onRemove).toBeCalled();

  const collapseIconButton = screen.getByTitle('Collapse');
  fireEvent.click(collapseIconButton);
  expect(onCollapse).toBeCalled();
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
