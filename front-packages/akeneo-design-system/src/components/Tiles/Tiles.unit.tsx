import React from 'react';
import {Tile, Tiles} from './Tiles';
import {render, screen, fireEvent} from '../../storybook/test-util';
import {AssetCollectionIcon} from '../../icons';

test('it renders tile correctly', () => {
  render(
    <Tiles size={'big'}>
      <Tile icon={<AssetCollectionIcon />}>A label</Tile>
    </Tiles>
  );

  expect(screen.getByText('A label')).toBeInTheDocument();
});

test('it fails when there are invalid children', () => {
  const mockConsole = jest.spyOn(console, 'error').mockImplementation();

  expect(() => {
    render(
      <Tiles>
        tata
        <span>span tag</span>
      </Tiles>
    );
  }).toThrowError();

  mockConsole.mockRestore();
});

test('Tiles supports forwardRef', () => {
  const ref = {current: null};

  render(<Tiles ref={ref} />);
  expect(ref.current).not.toBe(null);
});

test('Tiles supports ...rest props', () => {
  render(<Tiles data-testid="my_value" />);
  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});

test('It can be small', () => {
  render(
    <Tiles size={'small'}>
      <Tile icon={<AssetCollectionIcon />}>small</Tile>
    </Tiles>
  );
  expect(screen.getByText('small')).toBeInTheDocument();
});

test('It can be big', () => {
  render(
    <Tiles size={'big'}>
      <Tile icon={<AssetCollectionIcon />}>big</Tile>
    </Tiles>
  );
  expect(screen.getByText('big')).toBeInTheDocument();
});

test('It can be inline and small', () => {
  render(
    <Tiles size={'small'} inline={true}>
      <Tile>inline</Tile>
    </Tiles>
  );
  expect(screen.getByText('inline')).toBeInTheDocument();
});

test('It can be inline and big', () => {
  render(
    <Tiles size={'small'} inline={true}>
      <Tile>inline</Tile>
    </Tiles>
  );
  expect(screen.getByText('inline')).toBeInTheDocument();
});

test('it triggers onclick when pressing enter with focus', () => {
  const handleClick = jest.fn();

  render(
    <Tiles size={'big'}>
      <Tile icon={<AssetCollectionIcon />} onClick={handleClick}>
        A label
      </Tile>
    </Tiles>
  );

  expect(screen.getByText('A label')).toBeInTheDocument();
  const input = screen.getByText('A label') as HTMLInputElement;
  fireEvent.keyDown(input, {key: 'Enter', code: 'Enter'});
  expect(handleClick).toBeCalled();
});
