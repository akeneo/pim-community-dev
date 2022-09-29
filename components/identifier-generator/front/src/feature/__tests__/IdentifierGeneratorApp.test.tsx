import React from 'react';
import {IdentifierGeneratorApp} from '../IdentifierGeneratorApp';
import {render, screen, act, fireEvent} from '../tests/test-utils';

jest.mock('@akeneo-pim-community/shared', () => ({
  ...jest.requireActual('@akeneo-pim-community/shared'),
  useTranslate: () => ((key: string) =>  key)
}));

describe('IdentifierGeneratorApp', () => {
  it('is just an example of unit test', () => {
    render(<IdentifierGeneratorApp />);

    expect(screen.getAllByText('pim_title.akeneo_identifier_generator_index')).toHaveLength(2);
    act(() => {
      fireEvent.click(screen.getByText('pim_common.create'));
    });
  });
});
