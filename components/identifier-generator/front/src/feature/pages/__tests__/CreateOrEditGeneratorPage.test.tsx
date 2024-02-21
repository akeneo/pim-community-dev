import React from 'react';
import {fireEvent, mockACLs, render, screen} from '../../tests/test-utils';
import {CreateOrEditGeneratorPage} from '../';
import {createMemoryHistory} from 'history';
import {Router} from 'react-router';
import {IdentifierGeneratorContext} from '../../context';
import initialGenerator from '../../tests/fixtures/initialGenerator';
import {IdentifierGenerator, PROPERTY_NAMES} from '../../models';

jest.mock('../DeleteGeneratorModal');
jest.mock('../../tabs/GeneralPropertiesTab');
jest.mock('../../tabs/SelectionTab');
jest.mock('../../tabs/StructureTab');

describe('CreateOrEditGeneratorPage', () => {
  it('should switch tabs', () => {
    const mainButtonCallback = jest.fn();
    render(
      <CreateOrEditGeneratorPage
        isMainButtonDisabled={false}
        initialGenerator={initialGenerator}
        validationErrors={[]}
        mainButtonCallback={mainButtonCallback}
        isNew={false}
      />
    );

    expect(screen.getByText('GeneratorPropertiesMock')).toBeInTheDocument();

    fireEvent.click(screen.getByText('pim_identifier_generator.tabs.product_selection'));
    expect(screen.getByText('SelectionTabMock')).toBeInTheDocument();

    fireEvent.click(screen.getByText('pim_identifier_generator.tabs.identifier_structure'));
    expect(screen.getByText('StructureTabMock')).toBeInTheDocument();

    fireEvent.click(screen.getByText('pim_identifier_generator.tabs.general'));
    expect(screen.getByText('GeneratorPropertiesMock')).toBeInTheDocument();
  });

  it('should call callback', () => {
    const mainButtonCallback = jest.fn();
    render(
      <CreateOrEditGeneratorPage
        isMainButtonDisabled={false}
        initialGenerator={initialGenerator}
        validationErrors={[]}
        mainButtonCallback={mainButtonCallback}
        isNew={false}
      />
    );

    fireEvent.click(screen.getByText('pim_common.save'));
    expect(mainButtonCallback).toBeCalledWith(initialGenerator);
  });

  it('should delete a generator', () => {
    const history = createMemoryHistory();
    render(
      <Router history={history}>
        <CreateOrEditGeneratorPage
          isMainButtonDisabled={false}
          initialGenerator={initialGenerator}
          validationErrors={[]}
          mainButtonCallback={jest.fn()}
          isNew={false}
        />
      </Router>
    );

    expect(screen.queryByText('pim_identifier_generator.deletion.operations')).toBeNull();

    const otherActionButton = screen.getAllByRole('button')[0];
    fireEvent.click(otherActionButton);
    fireEvent.click(screen.getByText('pim_common.delete'));
    expect(screen.getByText('DeleteGeneratorModalMock')).toBeInTheDocument();
    fireEvent.click(screen.getByText('Delete generator'));
    expect(history.location.pathname).toBe('/');
  });

  it('should update a generator on structure change', () => {
    const mockSetHasUnsavedChanges = jest.fn();
    const identifierGeneratorDependencies = {
      unsavedChanges: {
        hasUnsavedChanges: false,
        setHasUnsavedChanges: mockSetHasUnsavedChanges,
      },
    };

    render(
      <IdentifierGeneratorContext.Provider value={identifierGeneratorDependencies}>
        <CreateOrEditGeneratorPage
          isMainButtonDisabled={false}
          initialGenerator={initialGenerator}
          validationErrors={[]}
          mainButtonCallback={jest.fn()}
          isNew={false}
        />
      </IdentifierGeneratorContext.Provider>
    );

    fireEvent.click(screen.getByText('pim_identifier_generator.tabs.identifier_structure'));
    expect(screen.getByText('StructureTabMock')).toBeInTheDocument();
    expect(screen.getByText('[{"type":"free_text","string":"AKN"}]')).toBeInTheDocument();
    fireEvent.click(screen.getByText('Update Free Text'));
    expect(screen.getByText('[{"type":"free_text","string":"Updated string"}]')).toBeInTheDocument();

    expect(mockSetHasUnsavedChanges).toHaveBeenCalledWith(true);

    fireEvent.click(screen.getByText('Revert Free Text'));
    expect(mockSetHasUnsavedChanges).toHaveBeenCalledWith(false);
  });

  it('should update generator on initialGenerator change', () => {
    const {rerender} = render(
      <CreateOrEditGeneratorPage
        isMainButtonDisabled={false}
        initialGenerator={initialGenerator}
        mainButtonCallback={jest.fn()}
        validationErrors={[]}
        isNew={false}
      />
    );

    expect(screen.getByText('pim_identifier_generator.tabs.identifier_structure')).toBeInTheDocument();
    fireEvent.click(screen.getByText('pim_identifier_generator.tabs.identifier_structure'));
    expect(screen.getByText('StructureTabMock')).toBeInTheDocument();
    expect(screen.getByText('[{"type":"free_text","string":"AKN"}]')).toBeInTheDocument();

    const updatedGenerator: IdentifierGenerator = {
      ...initialGenerator,
      structure: [{type: PROPERTY_NAMES.FREE_TEXT, string: 'Updated string'}],
    };

    rerender(
      <CreateOrEditGeneratorPage
        isMainButtonDisabled={false}
        initialGenerator={updatedGenerator}
        mainButtonCallback={jest.fn()}
        validationErrors={[]}
        isNew={false}
      />
    );
    expect(screen.getByText('pim_identifier_generator.tabs.identifier_structure')).toBeInTheDocument();
    fireEvent.click(screen.getByText('pim_identifier_generator.tabs.identifier_structure'));
    expect(screen.getByText('[{"type":"free_text","string":"Updated string"}]')).toBeInTheDocument();
  });

  it('should update delimiter', () => {
    render(
      <CreateOrEditGeneratorPage
        isMainButtonDisabled={false}
        initialGenerator={initialGenerator}
        validationErrors={[]}
        mainButtonCallback={jest.fn()}
        isNew={false}
      />
    );

    fireEvent.click(screen.getByText('pim_identifier_generator.tabs.identifier_structure'));
    expect(screen.getByText('StructureTabMock')).toBeInTheDocument();
    expect(screen.getByText('Delimiter is -')).toBeInTheDocument();
    fireEvent.click(screen.getByText('Update Delimiter'));
    expect(screen.getByText('Delimiter is /')).toBeInTheDocument();
  });

  it('should reset delimiter when all properties are removed', () => {
    render(
      <CreateOrEditGeneratorPage
        isMainButtonDisabled={false}
        initialGenerator={initialGenerator}
        validationErrors={[]}
        mainButtonCallback={jest.fn()}
        isNew={false}
      />
    );

    fireEvent.click(screen.getByText('pim_identifier_generator.tabs.identifier_structure'));
    expect(screen.getByText('StructureTabMock')).toBeInTheDocument();
    expect(screen.getByText('Delimiter is -')).toBeInTheDocument();

    fireEvent.click(screen.getByText('Delete Free Text'));
    expect(expect(screen.getByText('Delimiter is null')).toBeInTheDocument());
  });

  it('should update conditions', () => {
    render(
      <CreateOrEditGeneratorPage
        isMainButtonDisabled={false}
        initialGenerator={initialGenerator}
        validationErrors={[]}
        mainButtonCallback={jest.fn()}
        isNew={false}
      />
    );

    fireEvent.click(screen.getByText('pim_identifier_generator.tabs.product_selection'));
    expect(screen.getByText('SelectionTabMock')).toBeInTheDocument();
    expect(screen.getByText('[{"type":"enabled","value":true}]')).toBeInTheDocument();
    fireEvent.click(screen.getByText('Update selection'));
    expect(screen.getByText('[{"type":"enabled","value":false}]')).toBeInTheDocument();
  });

  it('should disallow saving or deleting a generator if ACL is not granted', () => {
    mockACLs(true, false);

    render(
      <CreateOrEditGeneratorPage
        isMainButtonDisabled={false}
        initialGenerator={initialGenerator}
        validationErrors={[]}
        mainButtonCallback={jest.fn()}
        isNew={false}
      />
    );

    expect(screen.queryByText('pim_common.save')).not.toBeInTheDocument();
    expect(screen.queryByText('pim_common.delete')).not.toBeInTheDocument();
  });
});
