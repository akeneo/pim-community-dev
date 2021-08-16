import React from 'react';
import {TableInputBody} from './TableInputBody';
import {render, screen} from '../../../../storybook/test-util';
import {TableInputRow} from '../TableInputRow/TableInputRow';

test('it renders its children properly', () => {
  render(
    <table>
      <TableInputBody>
        <TableInputRow>
          <td>Cell content</td>
        </TableInputRow>
      </TableInputBody>
    </table>
  );

  expect(screen.getByText('Cell content')).toBeInTheDocument();
});

test('it does not render invalid rows', () => {
  render(
    <table>
      <TableInputBody>Cell content</TableInputBody>
    </table>
  );

  expect(screen.queryByText('Cell content')).not.toBeInTheDocument();
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
