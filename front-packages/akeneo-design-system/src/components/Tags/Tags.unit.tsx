import React from 'react';
import {Tag, Tags} from './Tags';
import {render, screen} from '../../storybook/test-util';

test('it renders its children properly', () => {
  render(
    <Tags>
      <Tag color="red">yolo</Tag>
    </Tags>
  );

  expect(screen.getByText('yolo')).toBeInTheDocument();
});

test('it fails when there are invalid children', () => {
  const mockConsole = jest.spyOn(console, 'error').mockImplementation();

  expect(() => {
    render(
      <Tags>
        tata
        <span>yolo</span>
      </Tags>
    );
  }).toThrowError();

  mockConsole.mockRestore();
});

// Those tests should pass directly if you follow the contributing guide.
// If you add required props to your Component, these tests will fail
// and you will need to add these required props here as well
test('Tags supports forwardRef', () => {
  const ref = {current: null};

  render(<Tags ref={ref} />);
  expect(ref.current).not.toBe(null);
});

test('Tags supports ...rest props', () => {
  render(<Tags data-testid="my_value" />);
  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});
