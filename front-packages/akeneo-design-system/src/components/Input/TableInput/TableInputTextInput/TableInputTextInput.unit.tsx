import React from 'react';
import {TableInputTextInput} from './TableInputTextInput';
import {render, screen} from '../../../../storybook/test-util';

describe('TableInputTextInput supports forwardRef', () => {
  const handleChange = jest.fn();
  const ref = {current: null};

  render(<TableInputTextInput id="myInput" value="Nice" onChange={handleChange} ref={ref} />);
  expect(ref.current).not.toBe(null);
});

test('TableInputTextInput supports ...rest props', () => {
  const handleChange = jest.fn();

  render(
    <TableInputTextInput id="myInput" value="Nice" onChange={handleChange} data-testid="my_value" highlighted={true} />
  );
  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});
