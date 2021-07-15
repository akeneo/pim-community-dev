import React from 'react';
import {TableInputHeader} from './TableInputHeader';
import {render, screen} from '../../../../storybook/test-util';

test('it renders its children properly', () => {
  render(
    <table>
      <TableInputHeader>
        <td>Cell content</td>
      </TableInputHeader>
    </table>
  );

  expect(screen.getByText('Cell content')).toBeInTheDocument();
});

describe('TableInputHeader supports forwardRef', () => {
  const ref = {current: null};

  render(
    <table>
      <TableInputHeader ref={ref} />
    </table>
  );
  expect(ref.current).not.toBe(null);
});

test('TableInputHeader supports ...rest props', () => {
  render(
    <table>
      <TableInputHeader data-testid="my_value" />
    </table>
  );
  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});
