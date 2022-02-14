import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders, ValidationError} from '@akeneo-pim-community/shared';
import {AttributeTarget, PropertyTarget} from 'feature/models';
import {TargetParameters} from './TargetParameters';

jest.mock('./AttributeTargetParameters', () => ({
  AttributeTargetParameters: ({onTargetChange}: {onTargetChange: () => void}) => (
    <div onClick={onTargetChange}>Attribute Target Parameters</div>
  ),
}));

jest.mock('./PropertyTargetParameters', () => ({
  PropertyTargetParameters: ({onTargetChange}: {onTargetChange: () => void}) => (
    <div onClick={onTargetChange}>Property Target Parameters</div>
  ),
}));

const attributeTarget: AttributeTarget = {
  code: 'description',
  type: 'attribute',
  action: 'set',
  if_empty: 'skip',
  channel: 'ecommerce',
  locale: 'fr_FR',
};

const propertyTarget: PropertyTarget = {
  code: 'parent',
  type: 'property',
  action: 'set',
  if_empty: 'skip',
};

test('it displays Property target parameters when target is a property and handles change', () => {
  const handleTargetChange = jest.fn();

  renderWithProviders(
    <TargetParameters target={propertyTarget} validationErrors={[]} onTargetChange={handleTargetChange} />
  );

  expect(screen.getByText('akeneo.tailored_import.data_mapping.target.title')).toBeInTheDocument();
  expect(screen.queryByText('Attribute Target Parameters')).not.toBeInTheDocument();

  userEvent.click(screen.getByText('Property Target Parameters'));

  expect(handleTargetChange).toHaveBeenCalled();
});

test('it displays Attribute target parameters when target is an attribute and handles change', () => {
  const handleTargetChange = jest.fn();

  renderWithProviders(
    <TargetParameters target={attributeTarget} validationErrors={[]} onTargetChange={handleTargetChange} />
  );

  expect(screen.getByText('akeneo.tailored_import.data_mapping.target.title')).toBeInTheDocument();
  expect(screen.queryByText('Property Target Parameters')).not.toBeInTheDocument();

  userEvent.click(screen.getByText('Attribute Target Parameters'));

  expect(handleTargetChange).toHaveBeenCalled();
});

test('it displays validation errors for a target code', () => {
  const validationErrors: ValidationError[] = [
    {
      messageTemplate: 'error.key.a_code_error',
      invalidValue: '',
      message: 'this is a code error',
      parameters: {},
      propertyPath: '[code]',
    },
    {
      messageTemplate: 'error.key.another_error',
      invalidValue: '',
      message: 'this is another error',
      parameters: {},
      propertyPath: '[another]',
    },
  ];

  renderWithProviders(
    <TargetParameters target={attributeTarget} validationErrors={validationErrors} onTargetChange={jest.fn()} />
  );

  expect(screen.getByText('error.key.a_code_error')).toBeInTheDocument();
  expect(screen.queryByText('error.key.another_error')).not.toBeInTheDocument();
});
