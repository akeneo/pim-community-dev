import React from 'react';
import {Tree} from './Tree';
import {render, screen, fireEvent} from '../../storybook/test-util';

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

test('it triggers onSelect callback', () => {
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

  expect(handleOpen).toBeCalledTimes(0);
});

test('It throws with an unknown status', () => {
  const mockConsole = jest.spyOn(console, 'error').mockImplementation();

  expect(() => {
    render(
      <Tree value={'master'} label={'Master'}>
        WrongNode
      </Tree>
    );
  }).toThrow();

  mockConsole.mockRestore();
});
