import React from 'react';
import {fireEvent, render, screen} from '../../storybook/test-util';
import {Block} from './Block';
import {IconButton} from '../IconButton/IconButton';
import {PlusIcon, CloseIcon, EditIcon} from '../../icons';

test('it renders without actions', () => {
  render(<Block>My block</Block>);

  expect(screen.getByText('My block')).toBeInTheDocument();
});

test('it renders actions passed by props', () => {
  const onRemove = jest.fn();
  const onEdit = jest.fn();

  render(
    <Block
      actions={
        <>
          <IconButton
            level="tertiary"
            ghost="borderless"
            size="small"
            key="edit"
            icon={<EditIcon />}
            title="Edit"
            onClick={onEdit}
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

  const editIconButton = screen.getByTitle('Edit');
  fireEvent.click(editIconButton);
  expect(onEdit).toBeCalled();

  const removeIconButton = screen.getByTitle('Remove');
  fireEvent.click(removeIconButton);
  expect(onRemove).toBeCalled();
});

test('it supports collapsing', () => {
  const onCollapse = jest.fn();
  const isOpen = false;

  render(
    <Block isOpen={isOpen} onCollapse={onCollapse} collapseButtonLabel="Collapse" actions={<></>}>
      My block
    </Block>
  );

  const collapseIconButton = screen.getByTitle('Collapse');
  fireEvent.click(collapseIconButton);
  expect(onCollapse).toBeCalled();
  expect(screen.getByText('Example content')).toBeInTheDocument();
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
