import React from 'react';
import {Field} from './Field';
import {render, screen} from '../../storybook/test-util';
import {TextInput, Helper} from '../../components';

test('it renders its children properly', () => {
  render(
    <Field label="Nice field">
      <TextInput data-testid="text-input" value="Coucou" />
      <Helper>Some info</Helper>
    </Field>
  );

  expect(screen.getByTestId('text-input')).toHaveAttribute('value', 'Coucou');
  expect(screen.getByText('Some info')).toBeInTheDocument();
});

test('it does not render something else than an Input or Helpers', () => {
  render(
    // @ts-expect-error Something else should not be displayed
    <Field label="Nice field" locale="en_US" channel="ecommerce">
      Something else
      <TextInput data-testid="text-input" value="Coucou" />
      <Helper>Some info</Helper>
      <Helper level="error">Another one</Helper>
    </Field>
  );

  expect(screen.getByTestId('text-input')).toBeInTheDocument();
  expect(screen.getByText('Some info')).toBeInTheDocument();
  expect(screen.getByText('Another one')).toBeInTheDocument();
  expect(screen.queryByText('ecommerce')).toBeInTheDocument();
  expect(screen.queryByText('en_US')).toBeInTheDocument();
  expect(screen.queryByText('Something else')).not.toBeInTheDocument();
});

test('Field supports forwardRef', () => {
  const ref = {current: null};

  render(
    <Field label="Nice field" ref={ref}>
      <TextInput data-testid="text-input" value="Coucou" />
    </Field>
  );
  expect(ref.current).not.toBe(null);
});

test('Field supports ...rest props', () => {
  render(
    <Field label="Nice field" data-testid="my_value">
      <TextInput data-testid="text-input" value="Coucou" />
    </Field>
  );
  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});
