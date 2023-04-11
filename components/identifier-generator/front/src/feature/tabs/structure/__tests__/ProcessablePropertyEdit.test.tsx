import {fireEvent} from '@testing-library/react';
import React from 'react';
import {ProcessablePropertyEdit} from '../ProcessablePropertyEdit';
import {AbbreviationType, PROPERTY_NAMES} from '../../../models';
import {render, screen} from '../../../tests/test-utils';

describe('ProcessablePropertyEdit', () => {
  const options = [
    {value: AbbreviationType.TRUNCATE, label: 'Truncate'},
    {value: AbbreviationType.NO, label: 'No abbreviation'},
    {value: AbbreviationType.NOMENCLATURE, label: 'Nomenclature'},
  ];

  const selectedProperty = {type: PROPERTY_NAMES.FAMILY, process: {type: AbbreviationType.NO}};

  it('should call onChange with the correct process type when Nomenclature value is selected', () => {
    const onChangeMock = jest.fn();

    render(
      <ProcessablePropertyEdit
        // @ts-ignore
        selectedProperty={selectedProperty}
        onChange={onChangeMock}
        options={options}
      />
    );

    expect(screen.getByText('pim_identifier_generator.structure.settings.abbrev_type')).toBeInTheDocument();
    const input = screen.getByTitle('pim_common.open');
    expect(input).toBeInTheDocument();

    fireEvent.click(input);
    const codeOption = screen.getByText('Nomenclature');
    expect(codeOption).toBeInTheDocument();
    fireEvent.click(codeOption);

    expect(onChangeMock).toHaveBeenCalledWith({
      type: PROPERTY_NAMES.FAMILY,
      process: {
        type: AbbreviationType.NOMENCLATURE,
      },
    });
  });

  it('should call onChange with the correct process type when Truncate value is selected', () => {
    const onChangeMock = jest.fn();

    render(
      <ProcessablePropertyEdit
        // @ts-ignore
        selectedProperty={selectedProperty}
        onChange={onChangeMock}
        options={options}
      />
    );

    expect(screen.getByText('pim_identifier_generator.structure.settings.abbrev_type')).toBeInTheDocument();
    const input = screen.getByTitle('pim_common.open');
    expect(input).toBeInTheDocument();

    fireEvent.click(input);
    const codeOption = screen.getByText('Truncate');
    expect(codeOption).toBeInTheDocument();
    fireEvent.click(codeOption);

    expect(onChangeMock).toHaveBeenCalledWith({
      type: PROPERTY_NAMES.FAMILY,
      process: {
        type: AbbreviationType.TRUNCATE,
        operator: null,
        value: 3,
      },
    });
  });

  it('should call onChange with the correct process type when No abbreviation value is selected', () => {
    const onChangeMock = jest.fn();

    render(
      <ProcessablePropertyEdit
        // @ts-ignore
        selectedProperty={{type: PROPERTY_NAMES.FAMILY, process: {type: AbbreviationType.NOMENCLATURE}}}
        onChange={onChangeMock}
        options={options}
      />
    );

    expect(screen.getByText('pim_identifier_generator.structure.settings.abbrev_type')).toBeInTheDocument();
    const input = screen.getByTitle('pim_common.open');
    expect(input).toBeInTheDocument();

    fireEvent.click(input);
    const codeOption = screen.getByText('No abbreviation');
    expect(codeOption).toBeInTheDocument();
    fireEvent.click(codeOption);

    expect(onChangeMock).toHaveBeenCalledWith({
      type: PROPERTY_NAMES.FAMILY,
      process: {
        type: AbbreviationType.NO,
      },
    });
  });
});
