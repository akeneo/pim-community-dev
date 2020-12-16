import React from 'react';
import {Locale} from './Locale';
import {render, screen} from '../../storybook/test-util';

test('it renders the emoji flag properly with language code if no label provided', () => {
  render(<Locale code="en_US" />);

  expect(screen.getByText('🇺🇸')).toBeInTheDocument();
  expect(screen.getByText('en')).toBeInTheDocument();
});

test('it renders the emoji flag properly with language label if provided', () => {
  render(<Locale code="en_US" languageLabel="English" />);

  expect(screen.getByText('🇺🇸')).toBeInTheDocument();
  expect(screen.getByText('English')).toBeInTheDocument();
});

test('Locale supports forwardRef', () => {
  const ref = {current: null};

  render(<Locale code="fr_FR" ref={ref} />);
  expect(ref.current).not.toBe(null);
});

test('Locale supports ...rest props', () => {
  render(<Locale code="fr_FR" data-testid="my_value" />);
  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});
