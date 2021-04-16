import React from 'react';
import {Tree} from './Tree';
import {render, screen, fireEvent} from '../../storybook/test-util';
import {Button} from '../Button/Button';

test('it renders tree with children properly and is loading', () => {
  render(
    <Tree value={'master'} label={'Master'} selected={true} selectable={true}>
      <Tree value={'camcorders'} label={'Camcorders'} isLoading={true} readOnly={true} />
      <Tree value={'radio'} label={'Radio'} isLeaf={true} selected={true} selectable={true} />
    </Tree>
  );

  expect(screen.getByText('Master')).toBeInTheDocument();
  expect(screen.getByText('Camcorders')).toBeInTheDocument();
});

test('Tree supports ...rest props', () => {
  render(<Tree value={'master'} label={'Master'} data-testid="my_value" />);
  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});

test('it triggers onOpen/onClose/onSelect callback', () => {
  const handleOpen = jest.fn();
  const handleClose = jest.fn();
  const handleChange = jest.fn();

  render(
    <Tree
      value={'master'}
      label={'Master'}
      selectable={true}
      onOpen={handleOpen}
      onClose={handleClose}
      onChange={handleChange}
    />
  );

  fireEvent.click(screen.getAllByRole('button')[0]); // Open
  fireEvent.click(screen.getAllByRole('button')[0]); // Close
  fireEvent.click(screen.getAllByRole('button')[1]); // Open
  fireEvent.click(screen.getAllByRole('button')[1]); // Close
  fireEvent.click(screen.getByRole('checkbox')); // Change

  expect(handleOpen).toBeCalledTimes(2);
  expect(handleOpen).toBeCalledWith('master');

  expect(handleClose).toBeCalledTimes(2);
  expect(handleClose).toBeCalledWith('master');

  expect(handleChange).toBeCalledTimes(1);
  expect(handleChange).toBeCalledWith('master', true, expect.anything());
});

test('it triggers onClick callback', () => {
  const handleOpen = jest.fn();
  const handleClose = jest.fn();
  const handleClick = jest.fn();

  render(
    <Tree
      value={'master'}
      label={'Master'}
      selectable={true}
      onOpen={handleOpen}
      onClose={handleClose}
      onClick={handleClick}
    />
  );

  fireEvent.click(screen.getAllByRole('button')[1]);

  expect(handleOpen).toBeCalledTimes(0);
  expect(handleClose).toBeCalledTimes(0);
  expect(handleClick).toBeCalledTimes(1);
  expect(handleClick).toBeCalledWith('master');
});

test('it does not trigger any callback when its a leaf', () => {
  const handleOpen = jest.fn();

  render(<Tree value={'master'} label={'Master'} isLeaf={true} onOpen={handleOpen} />);

  fireEvent.click(screen.getAllByRole('button')[0]);
  fireEvent.click(screen.getAllByRole('button')[1]);

  expect(handleOpen).toBeCalledTimes(0);
});

test('It does not render invalid children', () => {
  render(
    <Tree value={'master'} label={'Master'}>
      WrongNode
      <div>ValidNode</div>
      <Tree value={'child'} label={'Child'} />
      <Tree.Actions>Actions</Tree.Actions>
    </Tree>
  );

  expect(screen.queryByText(/WrongNode/)).not.toBeInTheDocument();
  expect(screen.queryByText(/ValidNode/)).toBeInTheDocument();
  expect(screen.queryByText(/Child/)).toBeInTheDocument();
  expect(screen.queryByText(/Actions/)).toBeInTheDocument();
});

test('it triggers actions', () => {
  const handleOpen = jest.fn();
  const handleClose = jest.fn();
  const handleClick = jest.fn();
  const handleAction = jest.fn();

  render(
    <Tree
      value={'master'}
      label={'Master'}
      selectable={true}
      onOpen={handleOpen}
      onClose={handleClose}
      onClick={handleClick}
    >
      <Tree.Actions>
        <Button onClick={handleAction} data-testid="action">
          Action
        </Button>
      </Tree.Actions>
    </Tree>
  );

  fireEvent.click(screen.getByTestId('action'));

  expect(handleOpen).toBeCalledTimes(0);
  expect(handleClose).toBeCalledTimes(0);
  expect(handleClick).toBeCalledTimes(0);
  expect(handleAction).toBeCalledTimes(1);

  jest.resetAllMocks();
  fireEvent.click(screen.getAllByRole('button')[0]);

  expect(handleOpen).toBeCalledTimes(1);
  expect(handleClose).toBeCalledTimes(0);
  expect(handleClick).toBeCalledTimes(0);
  expect(handleAction).toBeCalledTimes(0);

  jest.resetAllMocks();
  fireEvent.click(screen.getAllByRole('button')[1]);

  expect(handleOpen).toBeCalledTimes(0);
  expect(handleClose).toBeCalledTimes(0);
  expect(handleClick).toBeCalledTimes(1);
  expect(handleAction).toBeCalledTimes(0);
});
