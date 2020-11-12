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
    //@ts-expect-error This other child should be ignored
    <Breadcrumb>
      Other child
      <Breadcrumb.Step>Breadcrumb content</Breadcrumb.Step>
    </Breadcrumb>
  );

  expect(screen.getByText('Breadcrumb content')).toBeInTheDocument();
  expect(screen.queryByText('Other child')).not.toBeInTheDocument();
});

test('Breadcrumb supports ...rest props', () => {
  render(
    <Breadcrumb data-testid="my_value">
      <Breadcrumb.Step>Breadcrumb content</Breadcrumb.Step>
    </Breadcrumb>
  );

  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});
