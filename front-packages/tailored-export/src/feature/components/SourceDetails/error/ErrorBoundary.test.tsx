import React from 'react';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {ErrorBoundary} from './ErrorBoundary';
import {InvalidAssociationTypeSourceError, InvalidAttributeSourceError, InvalidPropertySourceError} from '../error';

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

test('it displays an invalid attribute placeholder the error is an InvalidAttributeSourceError', () => {
  const mockedConsole = jest.spyOn(console, 'error').mockImplementation();

  renderWithProviders(
    <ErrorBoundary>
      <ThrowingChild error={new InvalidAttributeSourceError('Invalid attribute source')} />
    </ErrorBoundary>
  );

  expect(
    screen.getByText('akeneo.tailored_export.column_details.sources.invalid_source.attribute')
  ).toBeInTheDocument();
  mockedConsole.mockRestore();
});

test('it displays an invalid property placeholder the error is an InvalidPropertySourceError', () => {
  const mockedConsole = jest.spyOn(console, 'error').mockImplementation();

  renderWithProviders(
    <ErrorBoundary>
      <ThrowingChild error={new InvalidPropertySourceError('Invalid property source')} />
    </ErrorBoundary>
  );

  expect(screen.getByText('akeneo.tailored_export.column_details.sources.invalid_source.property')).toBeInTheDocument();
  mockedConsole.mockRestore();
});

test('it displays an invalid association type placeholder the error is an InvalidAssociationTypeSourceError', () => {
  const mockedConsole = jest.spyOn(console, 'error').mockImplementation();

  renderWithProviders(
    <ErrorBoundary>
      <ThrowingChild error={new InvalidAssociationTypeSourceError('Invalid association type source')} />
    </ErrorBoundary>
  );

  expect(
    screen.getByText('akeneo.tailored_export.column_details.sources.invalid_source.association_type')
  ).toBeInTheDocument();
  mockedConsole.mockRestore();
});
