import React from 'react';
import {TableInputBody} from './TableInputBody';
import {render, screen} from '../../../../storybook/test-util';

test('it renders its children properly', () => {
  render(
    <table>
      <TableInputBody>
        <tr>
          <td>Cell content</td>
        </tr>
      </TableInputBody>
    </table>
  );

  expect(screen.getByText('Cell content')).toBeInTheDocument();
});

describe('TableInputBody supports forwardRef', () => {
  const ref = {current: null};

  render(
    <table>
      <TableInputBody ref={ref} />
    </table>
  );
  expect(ref.current).not.toBe(null);
});

test('TableInputBody supports ...rest props', () => {
  render(
    <table>
      <TableInputBody data-testid="my_value" />
    </table>
  );
  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});
