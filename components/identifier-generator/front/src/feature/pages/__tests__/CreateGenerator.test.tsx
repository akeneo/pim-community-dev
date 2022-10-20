import React from 'react';
import {fireEvent, render, screen} from '../../tests/test-utils';
import {CreateGeneratorPage} from '../';
import {IdentifierGenerator} from '../../models';

jest.mock('../../tabs/GeneralPropertiesTab');

describe('CreateGenerator', () => {
  it('should switch tabs', () => {
    const initialGenerator: IdentifierGenerator = {
      code: 'initialCode',
      labels: {
        en_US: 'Initial Label',
      },
    };
    render(<CreateGeneratorPage initialGenerator={initialGenerator} />);
    expect(screen.getByText('GeneratorPropertiesMock')).toBeInTheDocument();

    fireEvent.click(screen.getByText('pim_identifier_generator.tabs.product_selection'));
    expect(screen.getByText('Not implemented YET')).toBeInTheDocument();

    fireEvent.click(screen.getByText('pim_identifier_generator.tabs.identifier_structure'));
    expect(screen.getByText('Not implemented YET')).toBeInTheDocument();

    fireEvent.click(screen.getByText('pim_identifier_generator.tabs.general'));
    expect(screen.getByText('GeneratorPropertiesMock')).toBeInTheDocument();
  });
});
