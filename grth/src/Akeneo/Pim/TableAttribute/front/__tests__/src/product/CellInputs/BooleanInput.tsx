import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {fireEvent, screen} from '@testing-library/react';
import {getComplexTableAttribute, getComplexTableConfiguration} from '../../../factories';
import BooleanInput from '../../../../src/product/CellInputs/BooleanInput';

describe('Boolean', () => {
  it('should render boolean', async () => {
    const booleanColumnDefinition = getComplexTableConfiguration()[2];
    renderWithProviders(
      <BooleanInput
        columnDefinition={booleanColumnDefinition}
        highlighted={false}
        inError={false}
        row={{'unique id': 'uniqueIdB', is_allergenic: true}}
        onChange={jest.fn()}
        attribute={getComplexTableAttribute()}
      />
    );

    expect(await screen.findByText('pim_common.yes')).toBeInTheDocument();
  });

  it('should reset boolean', () => {
    const handleChange = jest.fn();
    const booleanColumnDefinition = getComplexTableConfiguration()[2];
    renderWithProviders(
      <BooleanInput
        columnDefinition={booleanColumnDefinition}
        highlighted={false}
        inError={false}
        row={{'unique id': 'uniqueIdB', is_allergenic: true}}
        onChange={handleChange}
        attribute={getComplexTableAttribute()}
      />
    );

    fireEvent.click(screen.getByTitle('pim_common.clear'));
    expect(handleChange).toBeCalledWith(undefined);
  });
});
