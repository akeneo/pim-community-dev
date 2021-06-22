import React from 'react';
import {TableInputNumber} from './TableInputNumber';
import {render, screen} from '../../../../storybook/test-util';

describe('TableInputNumber supports forwardRef', () => {
  const handleChange = jest.fn();
  const ref = {current: null};

  render(<TableInputNumber id="myInput" value="12" onChange={handleChange} ref={ref} />);
  expect(ref.current).not.toBe(null);
});

test('TableInputNumber supports ...rest props', () => {
  const handleChange = jest.fn();

  render(
    <TableInputNumber id="myInput" value="12" onChange={handleChange} data-testid="my_value" highlighted={true} />
  );
  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});
