import React from 'react';
import {TableInputRow} from './TableInputRow';
import {render, screen} from '../../../../storybook/test-util';

test('it renders its children properly', () => {
  render(
    <table>
      <tbody>
        <TableInputRow>
          <td>Cell content</td>
        </TableInputRow>
      </tbody>
    </table>
  );

  expect(screen.getByText('Cell content')).toBeInTheDocument();
});

describe('TableInputRow supports forwardRef', () => {
  const ref = {current: null};

  render(
    <table>
      <tbody>
        <TableInputRow ref={ref} />
      </tbody>
    </table>
  );
  expect(ref.current).not.toBe(null);
});

test('TableInputRow supports ...rest props', () => {
  render(
    <table>
      <tbody>
        <TableInputRow data-testid="my_value" />
      </tbody>
    </table>
  );
  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});
