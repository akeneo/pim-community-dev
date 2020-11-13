import React from 'react';
import {Breadcrumb} from './Breadcrumb';
import {render, screen} from '../../storybook/test-util';
import {Link} from '..';

test('it renders its children properly', () => {
  render(
    <Breadcrumb color="blue">
      <Breadcrumb.Item href={'https://my-website/settings'}>Settings</Breadcrumb.Item>
      <Breadcrumb.Item href={'https://my-website/settings/attributes'}>Attributes</Breadcrumb.Item>
      <Breadcrumb.Item href={'https://my-website/settings/attributes/brand'}>Brand</Breadcrumb.Item>
    </Breadcrumb>
  );

  expect(screen.getByText('Settings')).toBeInTheDocument();
  expect(screen.getByText('Attributes')).toBeInTheDocument();
  expect(screen.getByText('Brand')).toBeInTheDocument();
});

test('it renders only breadcrumb item', () => {
  jest.spyOn(global.console, 'error').mockImplementation(jest.fn());

  render(
    <Breadcrumb color="blue">
      <Link href={'https://my-website/settings'}>Settings</Link>
      <Breadcrumb.Item href={'https://my-website/settings/attributes'}>Attributes</Breadcrumb.Item>
    </Breadcrumb>
  );

  expect(screen.queryByText('Settings')).not.toBeInTheDocument();
  expect(screen.getByText('Attributes')).toBeInTheDocument();
});

// Those tests should pass directly if you follow the contributing guide.
// If you add required props to your Component, these tests will fail
// and you will need to add these required props here as well
//describe('Breadcrumb supports forwardRef', () => {
//  const ref = {current: null};

//  render(<Breadcrumb ref={ref} />);
//  expect(ref.current).not.toBe(null);
//});

describe('Breadcrumb supports ...rest props', () => {
  const {container} = render(<Breadcrumb color="blue" data-my-attribute="my_value" />);
  expect(container.querySelector('[data-my-attribute="my_value"]')).toBeInTheDocument();
});
