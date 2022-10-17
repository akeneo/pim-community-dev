import React from 'react';
import {fireEvent, render, screen} from '../../tests/test-utils';
import {CreateGenerator} from '../CreateGenerator';
import {IdentifierGenerator} from '../../../models';

jest.mock('@akeneo-pim-community/shared', () => ({
  ...jest.requireActual('@akeneo-pim-community/shared'),
  useTranslate: () => (key: string) => key,
}));
jest.mock('../edit/GeneralProperties');

describe('CreateGenerator', () => {
  it('should switch tabs', () => {
    const initialGenerator: IdentifierGenerator = {
      code: 'initialCode',
      labels: {
        'en_US': 'Initial Label'
      }
    };
    render(<CreateGenerator initialGenerator={initialGenerator}/>);
    expect(screen.getByText('GeneratorPropertiesMock')).toBeInTheDocument();

    fireEvent.click(screen.getByText('pim_identifier_generator.tabs.product_selection'));
    expect(screen.getByText('Not implemented YET')).toBeInTheDocument();

    fireEvent.click(screen.getByText('pim_identifier_generator.tabs.identifier_structure'));
    expect(screen.getByText('Not implemented YET')).toBeInTheDocument();

    fireEvent.click(screen.getByText('pim_identifier_generator.tabs.general'));
    expect(screen.getByText('GeneratorPropertiesMock')).toBeInTheDocument();
  });
});
