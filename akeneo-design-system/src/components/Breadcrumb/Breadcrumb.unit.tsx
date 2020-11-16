import React from 'react';
import {Breadcrumb, Item} from './Breadcrumb';
import {render, screen} from '../../storybook/test-util';

test('it renders its children properly', () => {
  render(
    <Breadcrumb>
      <Item>Foo</Item>
      <Item>Bar</Item>
    </Breadcrumb>
  );

  expect(screen.getByText('Foo')).toBeInTheDocument();
  expect(screen.getByText('Bar')).toBeInTheDocument();
  expect(screen.getAllByText('/')).toHaveLength(1);
});

// Those tests should pass directly if you follow the contributing guide.
// If you add required props to your Component, these tests will fail
// and you will need to add these required props here as well
test('Breadcrumb supports forwardRef', () => {
  const ref = {current: null};

  render(
    <Breadcrumb ref={ref}>
      <Item>Foo</Item>
    </Breadcrumb>
  );
  expect(ref.current).not.toBe(null);
});

test('Breadcrumb supports ...rest props', () => {
  const {container} = render(
    <Breadcrumb data-my-attribute="my_value">
      <Item>Foo</Item>
    </Breadcrumb>
  );
  expect(container.querySelector('[data-my-attribute="my_value"]')).toBeInTheDocument();
});

test('it renders its children properly', () => {
  render(
    <Breadcrumb>
      <div>Foo</div>
    </Breadcrumb>
  );

  expect(screen.queryByText('Foo')).not.toBeInTheDocument();
});
