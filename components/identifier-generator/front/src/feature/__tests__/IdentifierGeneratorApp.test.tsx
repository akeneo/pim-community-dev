import React from 'react';
import {screen, act, fireEvent} from '@testing-library/react';
import {IdentifierGeneratorApp} from '../IdentifierGeneratorApp';
import {renderWithProviders} from '@akeneo-pim-community/shared';

describe('IdentifierGeneratorApp', () => {
  it('is just an example of unit test', () => {
    renderWithProviders(<IdentifierGeneratorApp />);

    expect(screen.getAllByText('pim_title.akeneo_identifier_generator_index')).toHaveLength(2);
    act(() => {
      fireEvent.click(screen.getByText('pim_common.create'));
    });
  });
});
