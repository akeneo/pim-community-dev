import React from 'react';
import {Tile, Tiles} from './Tiles';
import {render, screen} from '../../storybook/test-util';
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
