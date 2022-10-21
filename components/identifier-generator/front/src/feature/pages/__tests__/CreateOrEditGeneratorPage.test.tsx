import React from 'react';
import {fireEvent, render, screen} from '../../tests/test-utils';
import {CreateOrEditGeneratorPage} from '../';
import {IdentifierGenerator, PROPERTY_NAMES} from '../../models';

jest.mock('../../tabs/GeneralPropertiesTab');

const initialGenerator: IdentifierGenerator = {
  code: 'initialCode',
  labels: {
    en_US: 'Initial Label',
  },
  conditions: [],
  structure: [{type: PROPERTY_NAMES.FREE_TEXT, string: 'AKN'}],
  delimiter: null,
  target: 'sku',
};

describe('CreateOrEditGeneratorPage', () => {
  it('should switch tabs', () => {
    const mainButtonCallback = jest.fn();
    render(
      <CreateOrEditGeneratorPage
        initialGenerator={initialGenerator}
        validationErrors={[]}
        mainButtonCallback={mainButtonCallback}
        mainButtonLabel={'Main button label'}
      />
    );

    expect(screen.getByText('Main button label')).toBeInTheDocument();
    expect(screen.getByText('GeneratorPropertiesMock')).toBeInTheDocument();

    fireEvent.click(screen.getByText('pim_identifier_generator.tabs.product_selection'));
    expect(screen.getByText('Not implemented YET')).toBeInTheDocument();
    expect(screen.getByText('[]')).toBeInTheDocument(); // conditions

    fireEvent.click(screen.getByText('pim_identifier_generator.tabs.identifier_structure'));
    expect(screen.getByText('Not implemented YET')).toBeInTheDocument();
    expect(screen.getByText('[{"type":"free_text","string":"AKN"}]')).toBeInTheDocument();

    fireEvent.click(screen.getByText('pim_identifier_generator.tabs.general'));
    expect(screen.getByText('GeneratorPropertiesMock')).toBeInTheDocument();
  });

  it('should call callback', () => {
    const mainButtonCallback = jest.fn();
    render(
      <CreateOrEditGeneratorPage
        initialGenerator={initialGenerator}
        validationErrors={[]}
        mainButtonCallback={mainButtonCallback}
        mainButtonLabel={'Main button label'}
      />
    );

    fireEvent.click(screen.getByText('Main button label'));
    expect(mainButtonCallback).toBeCalledWith(initialGenerator);
  });

  it('should display validation errors', () => {
    const mainButtonCallback = jest.fn();
    render(
      <CreateOrEditGeneratorPage
        initialGenerator={initialGenerator}
        validationErrors={[{message: 'a message', path: 'a path'}, {message: 'another message'}]}
        mainButtonCallback={mainButtonCallback}
        mainButtonLabel={'Main button label'}
      />
    );

    fireEvent.click(screen.getByText('Main button label'));
    expect(screen.getByText('a path: a message')).toBeInTheDocument();
    expect(screen.getByText('another message')).toBeInTheDocument();
  });
});
