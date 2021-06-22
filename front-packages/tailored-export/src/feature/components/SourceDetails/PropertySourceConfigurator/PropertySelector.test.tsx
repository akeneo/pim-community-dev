import React from 'react';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {PropertySelector} from './PropertySelector';

jest.mock('../Selector/CodeLabelCollectionSelector', () => ({
  CodeLabelCollectionSelector: () => 'This is a code and label collection selector',
}));

jest.mock('../Selector/CodeLabelSelector', () => ({
  CodeLabelSelector: () => 'This is a code and label selector',
}));

jest.mock('./PropertySelector/ParentSelector', () => ({
  ParentSelector: () => 'This is a parent selector',
}));

test('it renders a parent selector for the parent property', () => {
    const onSelectionChange = jest.fn();

    renderWithProviders(
      <PropertySelector
        propertyName='parent'
        selection={{type: 'code'}}
        validationErrors={[]}
        onSelectionChange={onSelectionChange}
      />
    );

    expect(screen.getByText('This is a parent selector')).toBeInTheDocument();
  }
);

test.each(['categories', 'groups'])('it renders a code label collection selector for "%s" property', propertyName => {
  const onSelectionChange = jest.fn();

  renderWithProviders(
    <PropertySelector
      propertyName={propertyName}
      validationErrors={[]}
      selection={{type: 'code', separator: ','}}
      onSelectionChange={onSelectionChange}
    />
  );

  expect(screen.getByText('This is a code and label collection selector')).toBeInTheDocument();
});

test.each(['family', 'family_variant'])('it renders a code label selector for "%s" property', propertyName => {
  const onSelectionChange = jest.fn();

  renderWithProviders(
    <PropertySelector
      propertyName={propertyName}
      validationErrors={[]}
      selection={{type: 'code'}}
      onSelectionChange={onSelectionChange}
    />
  );

  expect(screen.getByText('This is a code and label selector')).toBeInTheDocument();
});
