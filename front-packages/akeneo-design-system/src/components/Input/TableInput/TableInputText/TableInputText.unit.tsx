import React from 'react';
import {TableInputText} from './TableInputText';
import {render, screen} from '../../../../storybook/test-util';

describe('TableInputText supports forwardRef', () => {
  const handleChange = jest.fn();
  const ref = {current: null};

  render(<TableInputText id="myInput" value="Nice" onChange={handleChange} ref={ref} />);
  expect(ref.current).not.toBe(null);
});

test('TableInputText supports ...rest props', () => {
  const handleChange = jest.fn();

  render(
    <TableInputText id="myInput" value="Nice" onChange={handleChange} data-testid="my_value" highlighted={true} />
  );
  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});
