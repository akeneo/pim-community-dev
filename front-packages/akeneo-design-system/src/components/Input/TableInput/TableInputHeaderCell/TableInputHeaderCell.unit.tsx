import React from 'react';
import {TableInputHeaderCell} from './TableInputHeaderCell';
import {render, screen} from '../../../../storybook/test-util';

test('it renders its children properly', () => {
  render(
    <table>
      <thead>
        <tr>
          <TableInputHeaderCell>Cell content</TableInputHeaderCell>
        </tr>
      </thead>
    </table>
  );

  expect(screen.getByText('Cell content')).toBeInTheDocument();
});

describe('TableInputHeaderCell supports forwardRef', () => {
  const ref = {current: null};

  render(
    <table>
      <thead>
        <tr>
          <TableInputHeaderCell ref={ref} />
        </tr>
      </thead>
    </table>
  );
  expect(ref.current).not.toBe(null);
});

test('TableInputHeaderCell supports ...rest props', () => {
  render(
    <table>
      <thead>
        <tr>
          <TableInputHeaderCell data-testid="my_value" />
        </tr>
      </thead>
    </table>
  );
  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});
