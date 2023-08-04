import React from 'react';
import {Tag, Tags} from './Tags';
import {render, screen} from '../../storybook/test-util';

test('it renders its children properly', () => {
  render(
    <Tags>
      <Tag tint="dark_blue">Dark blue Tag</Tag>
    </Tags>
  );

  expect(screen.getByText('Dark blue Tag')).toBeInTheDocument();
});

test('it fails when there are invalid children', () => {
  const mockConsole = jest.spyOn(console, 'error').mockImplementation();

  expect(() => {
    render(
      <Tags>
        tata
        <span>span tag</span>
      </Tags>
    );
  }).toThrowError();

  mockConsole.mockRestore();
});

test('Tags supports forwardRef', () => {
  const ref = {current: null};

  render(<Tags ref={ref} />);
  expect(ref.current).not.toBe(null);
});

test('Tags supports ...rest props', () => {
  render(<Tags data-testid="my_value" />);
  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});
