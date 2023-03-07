import React from 'react';
import {render} from '../../../../tests/test-utils';
import {FamilyPropertyEdit} from '../FamilyPropertyEdit';
import {AbbreviationType, FamilyProperty, Operator, PROPERTY_NAMES} from '../../../../models';
import {fireEvent} from '@testing-library/react';

jest.mock('../../../../components/NomenclatureEdit');

describe('FamilyPropertyEdit', () => {
  it('should update the family property', () => {
    const familyProperty: FamilyProperty = {
      type: PROPERTY_NAMES.FAMILY,
      process: {
        type: null,
      },
    };
    const mockedOnChange = jest.fn();
    const screen = render(<FamilyPropertyEdit selectedProperty={familyProperty} onChange={mockedOnChange} />);

    expect(screen.getByText('pim_identifier_generator.structure.settings.abbrev_type')).toBeInTheDocument();
    expect(screen.queryByText('pim_identifier_generator.structure.settings.operator.title')).not.toBeInTheDocument();
    const input = screen.getByTitle('pim_common.open');
    expect(input).toBeInTheDocument();

    // With code option
    fireEvent.click(input);
    const codeOption = screen.getByText('pim_identifier_generator.structure.settings.code_format.type.code');
    expect(codeOption).toBeInTheDocument();
    fireEvent.click(codeOption);

    expect(mockedOnChange).toHaveBeenCalledWith({
      ...familyProperty,
      process: {
        type: AbbreviationType.NO,
      },
    });

    // With truncate option
    fireEvent.click(input);

    const truncateOption = screen.getByText('pim_identifier_generator.structure.settings.code_format.type.truncate');
    expect(truncateOption).toBeInTheDocument();
    fireEvent.click(truncateOption);

    expect(mockedOnChange).toHaveBeenCalledWith({
      ...familyProperty,
      process: {
        type: AbbreviationType.TRUNCATE,
        value: 3,
        operator: null,
      },
    });

    // With nomenclature option
    fireEvent.click(input);

    const nomenclatureOption = screen.getByText(
      'pim_identifier_generator.structure.settings.code_format.type.nomenclature'
    );
    expect(nomenclatureOption).toBeInTheDocument();
    fireEvent.click(nomenclatureOption);

    expect(mockedOnChange).toHaveBeenCalledWith({
      ...familyProperty,
      process: {
        type: AbbreviationType.NOMENCLATURE,
      },
    });
  });

  it('should display the operator and value for truncate option', () => {
    const familyProperty: FamilyProperty = {
      type: PROPERTY_NAMES.FAMILY,
      process: {
        type: AbbreviationType.TRUNCATE,
        operator: Operator.LOWER_OR_EQUAL_THAN,
        value: 3,
      },
    };
    const mockedOnChange = jest.fn();
    const screen = render(<FamilyPropertyEdit selectedProperty={familyProperty} onChange={mockedOnChange} />);

    expect(screen.getByText('pim_identifier_generator.structure.settings.abbrev_type')).toBeInTheDocument();
    expect(screen.getByText('pim_identifier_generator.structure.settings.operator.title')).toBeInTheDocument();
    expect(screen.getByText('pim_identifier_generator.structure.settings.chars_number')).toBeInTheDocument();

    fireEvent.click(screen.getByPlaceholderText('pim_identifier_generator.structure.settings.operator.placeholder'));
    expect(screen.getByText('pim_common.operators.=')).toBeInTheDocument();
    fireEvent.click(screen.getByText('pim_common.operators.='));
    expect(mockedOnChange).toHaveBeenCalledWith({
      ...familyProperty,
      process: {
        type: AbbreviationType.TRUNCATE,
        value: 3,
        operator: Operator.EQUALS,
      },
    });

    fireEvent.change(screen.getByTitle('3'), {target: {value: 4}});
    expect(mockedOnChange).toHaveBeenCalledWith({
      ...familyProperty,
      process: {
        type: AbbreviationType.TRUNCATE,
        operator: Operator.LOWER_OR_EQUAL_THAN,
        value: 4,
      },
    });
  });

  it('should display nomenclature edition when abbreviation type is nomenclature', () => {
    const validateFamilyProperty: FamilyProperty = {
      type: PROPERTY_NAMES.FAMILY,
      process: {
        type: AbbreviationType.NOMENCLATURE,
      },
    };
    const screen = render(<FamilyPropertyEdit selectedProperty={validateFamilyProperty} onChange={jest.fn()} />);

    expect(screen.getByText('NomenclatureEditMock')).toBeInTheDocument();
  });
});
