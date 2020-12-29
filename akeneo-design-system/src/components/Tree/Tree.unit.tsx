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

test('Tree supports forwardRef', () => {
  const ref = {current: null};

  render(<Tree value={'master'} label={'Master'} ref={ref} />);
  expect(ref.current).not.toBe(null);
});

test('Tree supports ...rest props', () => {
  render(<Tree value={'master'} label={'Master'} data-testid="my_value" />);
  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});

test('it triggers onOpen/onClose/onSelect callback', () => {
  const handleOpen = jest.fn();
  const handleSelect = jest.fn();
  const handleClose = jest.fn();

  render(
    <Tree
      value={'master'}
      label={'Master'}
      selectable={true}
      onOpen={handleOpen}
      onSelect={handleSelect}
      onClose={handleClose}
    />
  );

  fireEvent.click(screen.getAllByRole('button')[0]);
  fireEvent.click(screen.getByRole('checkbox'));
  fireEvent.click(screen.getAllByRole('button')[0]);
  fireEvent.click(screen.getAllByRole('button')[1]);
  fireEvent.click(screen.getAllByRole('button')[1]);

  expect(handleOpen).toBeCalledTimes(2);
  expect(handleOpen).toBeCalledWith('master');

  expect(handleSelect).toBeCalledTimes(1);
  expect(handleSelect).toBeCalledWith(true, expect.anything());

  expect(handleClose).toBeCalledTimes(2);
  expect(handleClose).toBeCalledWith('master');
});

test('it does not trigger any callback when its a leaf', () => {
  const handleOpen = jest.fn();

  render(<Tree value={'master'} label={'Master'} isLeaf={true} onOpen={handleOpen} />);

  fireEvent.click(screen.getAllByRole('button')[0]);

  expect(handleOpen).toBeCalledTimes(0);
});

test('It throws with an unknown status', () => {
  expect(() => {
    render(
      <Tree value={'master'} label={'Master'}>
        WrongNode
      </Tree>
    );
  }).toThrow();
});
