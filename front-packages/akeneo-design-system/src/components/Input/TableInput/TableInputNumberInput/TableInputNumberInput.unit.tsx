import React from 'react';
import {TableInputNumberInput} from './TableInputNumberInput';
import {render, screen} from '../../../../storybook/test-util';

describe('TableInputNumberInput supports forwardRef', () => {
  const handleChange = jest.fn();
  const ref = {current: null};

  render(<TableInputNumberInput id="myInput" value="12" onChange={handleChange} ref={ref} />);
  expect(ref.current).not.toBe(null);
});

test('TableInputNumberInput supports ...rest props', () => {
  const handleChange = jest.fn();

  render(
    <TableInputNumberInput id="myInput" value="12" onChange={handleChange} data-testid="my_value" highlighted={true} />
  );
  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});
