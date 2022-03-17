import React from 'react';
import {fireEvent, render, screen} from '../../storybook/test-util';
import {Block} from './Block';
import {IconButton} from '../IconButton/IconButton';
import {Button} from '../Button/Button';
import {PlusIcon, CloseIcon, EditIcon} from '../../icons';

test('it renders without actions', () => {
  render(<Block>My block</Block>);

  expect(screen.getByText('My block')).toBeInTheDocument();
});

test('it renders actions passed by props', () => {
  const onEdit = jest.fn();
  const onRemove = jest.fn();

  render(
    <Block
      actions={[
        <IconButton key="edit" icon={<EditIcon />} onClick={onEdit} title="Edit" />,
        <IconButton key="delete" icon={<CloseIcon />} onClick={onRemove} title="Remove" />,
        <Button key="add" title="Add" />,
      ]}
    >
      My block
    </Block>
  );

  const removeIconButton = screen.getByTitle('Remove');
  fireEvent.click(removeIconButton);
  expect(onRemove).toBeCalled();

  const editIconButton = screen.getByTitle('Edit');
  fireEvent.click(editIconButton);
  expect(onEdit).toBeCalled();

  const addButton = screen.getByTitle('Add');
  expect(addButton).toBeInTheDocument();
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
