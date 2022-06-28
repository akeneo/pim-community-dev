import React from 'react';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {ErrorBoundary} from './ErrorBoundary';
import {InvalidAttributeTargetError} from './Attribute/error/InvalidAttributeTargetError';
import {InvalidPropertyTargetError} from './Property/error/InvalidPropertyTargetError';

const ThrowingChild = ({error}: {error: Error}) => {
  throw error;
};

test('it displays a placeholder when catching an error', () => {
  const mockedConsole = jest.spyOn(console, 'error').mockImplementation();

  renderWithProviders(
    <ErrorBoundary>
      <ThrowingChild error={new Error('This is an error')} />
    </ErrorBoundary>
  );

  expect(screen.getByText('This is an error')).toBeInTheDocument();
  mockedConsole.mockRestore();
});

test('it displays an invalid attribute placeholder the error is an InvalidAttributeTargetError', () => {
  const mockedConsole = jest.spyOn(console, 'error').mockImplementation();

  renderWithProviders(
    <ErrorBoundary>
      <ThrowingChild error={new InvalidAttributeTargetError('Invalid attribute source')} />
    </ErrorBoundary>
  );

  expect(screen.getByText('akeneo.tailored_import.data_mapping.target.invalid.attribute')).toBeInTheDocument();
  mockedConsole.mockRestore();
});

test('it displays an invalid property placeholder the error is an InvalidPropertyTargetError', () => {
  const mockedConsole = jest.spyOn(console, 'error').mockImplementation();

  renderWithProviders(
    <ErrorBoundary>
      <ThrowingChild error={new InvalidPropertyTargetError('Invalid property source')} />
    </ErrorBoundary>
  );

  expect(screen.getByText('akeneo.tailored_import.data_mapping.target.invalid.property')).toBeInTheDocument();
  mockedConsole.mockRestore();
});
