import React from 'react';
import {TableInputCell} from './TableInputCell';
import {render, screen} from '../../../../storybook/test-util';

test('it renders its children properly', () => {
  render(
    <table>
      <tbody>
        <tr>
          <TableInputCell>Cell content</TableInputCell>
        </tr>
      </tbody>
    </table>
  );

  expect(screen.getByText('Cell content')).toBeInTheDocument();
});

describe('TableInputCell supports forwardRef', () => {
  const ref = {current: null};

  render(
    <table>
      <tbody>
        <tr>
          <TableInputCell ref={ref} />
        </tr>
      </tbody>
    </table>
  );
  expect(ref.current).not.toBe(null);
});

test('TableInputCell supports ...rest props', () => {
  render(
    <table>
      <tbody>
        <tr>
          <TableInputCell data-testid="my_value" />
        </tr>
      </tbody>
    </table>
  );
  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});
