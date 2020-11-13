import React from 'react';
import {Breadcrumb} from './Breadcrumb';
import {render, screen} from '../../storybook/test-util';

test('it renders its children properly', () => {
  render(<Breadcrumb color="blue">
    <Breadcrumb.Item href={"https://my-website/settings"}>Settings</Breadcrumb.Item>
    <Breadcrumb.Item href={"https://my-website/settings/attributes"}>Attributes</Breadcrumb.Item>
    <Breadcrumb.Item href={"https://my-website/settings/attributes/brand"}>Brand</Breadcrumb.Item>
  </Breadcrumb>);

  expect(screen.getByText('Settings')).toBeInTheDocument();
  expect(screen.getByText('Attributes')).toBeInTheDocument();
  expect(screen.getByText('Brand')).toBeInTheDocument();
});

// Those tests should pass directly if you follow the contributing guide.
// If you add required props to your Component, these tests will fail
// and you will need to add these required props here as well
describe('Breadcrumb supports forwardRef', () => {
  const ref = {current: null};

  render(<Breadcrumb ref={ref} />);
  expect(ref.current).not.toBe(null);
});

describe('Breadcrumb supports ...rest props', () => {
  const {container} = render(<Breadcrumb data-my-attribute="my_value" />);
  expect(container.querySelector('[data-my-attribute="my_value"]')).toBeInTheDocument();
});
