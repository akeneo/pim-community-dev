import React from 'react';
import {Breadcrumb} from './Breadcrumb';
import {render, screen} from '../../storybook/test-util';

test('it renders its children properly', () => {
  render(
    <Breadcrumb>
      <Breadcrumb.Step>Breadcrumb content</Breadcrumb.Step>
    </Breadcrumb>
  );

  expect(screen.getByText('Breadcrumb content')).toBeInTheDocument();
});

test('it does not render children that are not `Breadcrumb.Step`', () => {
  render(
    <Breadcrumb>
      Other child
      <Breadcrumb.Step>Breadcrumb content</Breadcrumb.Step>
    </Breadcrumb>
  );

  expect(screen.getByText('Breadcrumb content')).toBeInTheDocument();
  expect(screen.queryByText('Other child')).not.toBeInTheDocument();
});

describe('Breadcrumb supports ...rest props', () => {
  const {container} = render(<Breadcrumb data-my-attribute="my_value" />);
  expect(container.querySelector('[data-my-attribute="my_value"]')).toBeInTheDocument();
});
