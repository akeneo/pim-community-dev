import React from 'react';
import {fireEvent, render, screen, act} from '../../storybook/test-util';
import {Block} from './Block';
import {IconButton} from '../IconButton/IconButton';
import {CloseIcon, EditIcon, PlusIcon} from '../../icons';

test('it renders without actions', () => {
  render(<Block title="I am a block" />);

  expect(screen.getByText('I am a block')).toBeInTheDocument();
});

test('it renders actions passed by props', () => {
  const onRemove = jest.fn();
  const onEdit = jest.fn();

  render(
    <Block
      title="My block"
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
    />
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
  jest.useFakeTimers();

  render(
    <Block title="My block" isOpen={false} onCollapse={onCollapse} collapseButtonLabel="Collapse">
      I am a block
    </Block>
  );

  act(() => {
    fireEvent.click(screen.getByTitle('Collapse'));
    jest.runAllTimers();
  });

  expect(onCollapse).toBeCalled();
  expect(screen.getByText('I am a block')).toBeInTheDocument();
});

test('Block supports forwardRef', () => {
  const ref = {current: null};

  render(<Block title="My block" ref={ref} />);

  expect(ref.current).not.toBe(null);
});

test('Block supports ...rest props', () => {
  render(<Block title="My block" data-testid="my_value" />);

  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});

test('it renders children with icon', () => {
  const onCollapse = jest.fn();
  const isOpen = false;

  render(
    <Block title="My block" isOpen={isOpen} onCollapse={onCollapse} collapseButtonLabel="Collapse">
      <PlusIcon data-testid="children-icon" />
      Icon
    </Block>
  );

  expect(screen.getByText('Icon')).toBeInTheDocument();
  expect(screen.getByTestId('children-icon')).toBeInTheDocument();
});
