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

test('it removes the last step `href` if it is provided', () => {
  render(
    <Breadcrumb>
      <Breadcrumb.Step href="#">First</Breadcrumb.Step>
      <Breadcrumb.Step href="#">Second</Breadcrumb.Step>
    </Breadcrumb>
  );

  expect(screen.getByText('First')).toHaveAttribute('href', '#');
  expect(screen.getByText('Second')).toHaveAttribute('disabled');
});

test('it respects Breadcrumb accessibility standards', () => {
  render(
    <Breadcrumb>
      <Breadcrumb.Step href="#">First</Breadcrumb.Step>
      <Breadcrumb.Step href="#">Second</Breadcrumb.Step>
    </Breadcrumb>
  );

  expect(screen.getByText('First').parentElement).toHaveAttribute('aria-label', 'Breadcrumb');
  expect(screen.getByText('Second')).toHaveAttribute('aria-current', 'page');
});

test('it throws when passing children that are not `Breadcrumb.Step`', () => {
  const mockConsole = jest.spyOn(console, 'error').mockImplementation();

  expect(() => {
    render(
      //@ts-expect-error This other child should trigger an error
      <Breadcrumb>
        Other child
        <Breadcrumb.Step>Breadcrumb content</Breadcrumb.Step>
      </Breadcrumb>
    );
  }).toThrowError();

  mockConsole.mockRestore();
});

test('Breadcrumb supports ...rest props', () => {
  render(
    <Breadcrumb data-testid="my_value">
      <Breadcrumb.Step>Breadcrumb content</Breadcrumb.Step>
    </Breadcrumb>
  );

  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});
