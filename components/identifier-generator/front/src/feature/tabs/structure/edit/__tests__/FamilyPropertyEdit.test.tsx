import React from 'react';
import {render} from '../../../../tests/test-utils';
import {FamilyPropertyEdit} from '../FamilyPropertyEdit';
import {AbbreviationType, FamilyProperty, Operator, PROPERTY_NAMES} from '../../../../models';
import {fireEvent} from '@testing-library/react';

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

    expect(screen.getByText('pim_identifier_generator.structure.settings.family.abbrev_type')).toBeInTheDocument();
    expect(screen.queryByText('pim_identifier_generator.structure.settings.family.operator')).not.toBeInTheDocument();
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
  });

  it('should display the operator and value for truncate option', () => {
    const familyProperty: FamilyProperty = {
      type: PROPERTY_NAMES.FAMILY,
      process: {
        type: AbbreviationType.TRUNCATE,
        operator: Operator.EQUAL_OR_LESS,
        value: 3,
      },
    };
    const mockedOnChange = jest.fn();
    const screen = render(<FamilyPropertyEdit selectedProperty={familyProperty} onChange={mockedOnChange} />);

    expect(screen.getByText('pim_identifier_generator.structure.settings.family.abbrev_type')).toBeInTheDocument();
    expect(screen.getByText('pim_identifier_generator.structure.settings.family.operator')).toBeInTheDocument();
    expect(screen.getByText('pim_identifier_generator.structure.settings.family.chars_number')).toBeInTheDocument();

    fireEvent.click(screen.getByPlaceholderText('pim_identifier_generator.structure.settings.operator.placeholder'));
    expect(screen.getByText('pim_common.operators.=')).toBeInTheDocument();
    fireEvent.click(screen.getByText('pim_common.operators.='));
    expect(mockedOnChange).toHaveBeenCalledWith({
      ...familyProperty,
      process: {
        type: AbbreviationType.TRUNCATE,
        value: 3,
        operator: Operator.EQUAL,
      },
    });

    fireEvent.change(screen.getByTitle('3'), {target: {value: 4}});
    expect(mockedOnChange).toHaveBeenCalledWith({
      ...familyProperty,
      process: {
        type: AbbreviationType.TRUNCATE,
        operator: Operator.EQUAL_OR_LESS,
        value: 4,
      },
    });
  });
});
