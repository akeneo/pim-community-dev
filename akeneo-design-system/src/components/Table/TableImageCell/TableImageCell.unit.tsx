import React from 'react';
import {Table} from '../Table';
import {render, screen} from '../../../storybook/test-util';

test('it renders its children properly', () => {
  render(
    <Table>
      <Table.Body>
        <Table.Row>
          <Table.ImageCell src="image.png" alt="my image" />
        </Table.Row>
      </Table.Body>
    </Table>
  );

  expect(screen.getByAltText('my image')).toBeInTheDocument();
});

test('it renders stacked image', () => {
  render(
    <Table>
      <Table.Body>
        <Table.Row>
          <Table.ImageCell isStacked src="image.png" alt="my image" />
        </Table.Row>
      </Table.Body>
    </Table>
  );

  expect(screen.getByAltText('my image')).toBeInTheDocument();
});

test('Table.ImageCell supports forwardRef', () => {
  const ref = {current: null};
  render(
    <Table>
      <Table.Body>
        <Table.Row>
          <Table.ImageCell src="image.png" alt="my image" ref={ref} />
        </Table.Row>
      </Table.Body>
    </Table>
  );

  expect(ref.current).not.toBe(null);
});

test('Table.ImageCell supports ...rest props', () => {
  render(
    <Table>
      <Table.Body>
        <Table.Row>
          <Table.ImageCell src="image.png" alt="my image" data-testid="my_value" />
        </Table.Row>
      </Table.Body>
    </Table>
  );

  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});
