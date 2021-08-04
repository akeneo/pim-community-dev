import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {QuantifiedAssociationTypeConfigurator} from './QuantifiedAssociationTypeConfigurator';
import {getDefaultQuantifiedAssociationTypeSource, QuantifiedAssociationTypeSelection} from './model';
import {AssociationType} from '../../../models';
import {getDefaultEnabledSource} from '../Enabled/model';

const associationType: AssociationType = {
  code: 'UPSELL',
  labels: {},
  is_quantified: false,
};

jest.mock('./QuantifiedAssociationTypeSelector', () => ({
  QuantifiedAssociationTypeSelector: ({
    onSelectionChange,
  }: {
    onSelectionChange: (updatedSelection: QuantifiedAssociationTypeSelection) => void;
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

test('it displays a quantified association type configurator and call handler when the type changes', () => {
  const onSourceChange = jest.fn();
  const source = getDefaultQuantifiedAssociationTypeSource(associationType);

  renderWithProviders(
    <QuantifiedAssociationTypeConfigurator source={source} validationErrors={[]} onSourceChange={onSourceChange} />
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

test('it does not render if the source is not valid', () => {
  const mockedConsole = jest.spyOn(console, 'error').mockImplementation();
  const onSourceChange = jest.fn();

  expect(() => {
    renderWithProviders(
      <QuantifiedAssociationTypeConfigurator
        source={getDefaultEnabledSource()}
        validationErrors={[]}
        onSourceChange={onSourceChange}
      />
    );
  }).toThrow('Invalid source data "enabled" for quantified association configurator');

  expect(screen.queryByText('Update selection')).not.toBeInTheDocument();
  mockedConsole.mockRestore();
});
