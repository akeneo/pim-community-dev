import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {SimpleAssociationTypeConfigurator} from './SimpleAssociationTypeConfigurator';
import {getDefaultSimpleAssociationTypeSource, SimpleAssociationTypeSelection} from './model';
import {AssociationType} from '../../../models';
import {getDefaultEnabledSource} from '../Enabled/model';

const associationType: AssociationType = {
  code: 'UPSELL',
  labels: {},
  is_quantified: false,
};

jest.mock('./SimpleAssociationTypeSelector', () => ({
  SimpleAssociationTypeSelector: ({
    onSelectionChange,
  }: {
    onSelectionChange: (updatedSelection: SimpleAssociationTypeSelection) => void;
  }) => (
    <button
      onClick={() =>
        onSelectionChange({
          type: 'code',
          entity_type: 'product_models',
          separator: ',',
        })
      }
    >
      Update selection
    </button>
  ),
}));

test('it displays a simple association type configurator', () => {
  const onSourceChange = jest.fn();
  const source = getDefaultSimpleAssociationTypeSource(associationType);

  renderWithProviders(
    <SimpleAssociationTypeConfigurator source={source} validationErrors={[]} onSourceChange={onSourceChange} />
  );

  userEvent.click(screen.getByText('Update selection'));

  expect(onSourceChange).toHaveBeenCalledWith({
    ...source,
    selection: {
      type: 'code',
      entity_type: 'product_models',
      separator: ',',
    },
  });
});

test('it tells when the source data is invalid', () => {
  const mockedConsole = jest.spyOn(console, 'error').mockImplementation();

  expect(() => {
    renderWithProviders(
      <SimpleAssociationTypeConfigurator
        source={getDefaultEnabledSource()}
        validationErrors={[]}
        onSourceChange={jest.fn()}
      />
    );
  }).toThrow('Invalid source data "enabled" for association configurator');

  expect(screen.queryByText('Update selection')).not.toBeInTheDocument();
  mockedConsole.mockRestore();
});
