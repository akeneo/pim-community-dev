import React from 'react';
import {mockResponse, render} from '../../../../tests/test-utils';
import {AttributePropertyEdit} from '../AttributePropertyEdit';
import {AbbreviationType, PROPERTY_NAMES, SimpleSelectProperty} from '../../../../models';
import {fireEvent} from '@testing-library/react';

jest.mock('../../../../components/ScopeAndLocaleSelector');

describe('AttributePropertyEdit', () => {
  it('should handle onChange values', () => {
    mockResponse('pim_enrich_attribute_rest_get', 'GET', {
      ok: true,
      json: {
        code: 'simple_select_localizable_scopable',
        labels: {en_US: 'simple_select_localizable_scopable'},
        localizable: true,
        scopable: true,
        type: 'pim_catalog_simpleselect',
      },
    });

    const simpleSelectProperty: SimpleSelectProperty = {
      type: PROPERTY_NAMES.SIMPLE_SELECT,
      attributeCode: 'simple_select',
      process: {
        type: null,
      },
    };
    const mockedOnChange = jest.fn();

    const screen = render(<AttributePropertyEdit selectedProperty={simpleSelectProperty} onChange={mockedOnChange} />);

    expect(screen.getByText('pim_identifier_generator.structure.settings.abbrev_type')).toBeInTheDocument();
    const input = screen.getByTitle('pim_common.open');
    expect(input).toBeInTheDocument();

    // With truncate option
    fireEvent.click(input);

    const truncateOption = screen.getByText('pim_identifier_generator.structure.settings.code_format.type.truncate');
    expect(truncateOption).toBeInTheDocument();
    fireEvent.click(truncateOption);

    expect(mockedOnChange).toHaveBeenCalledWith({
      ...simpleSelectProperty,
      process: {
        type: AbbreviationType.TRUNCATE,
        value: 3,
        operator: null,
      },
    });

    expect(screen.getByText('ScopeAndLocaleSelectorMock')).toBeInTheDocument();
    fireEvent.click(screen.getByText('Change values'));
    expect(mockedOnChange).toHaveBeenCalledWith({
      ...simpleSelectProperty,
      locale: 'new_locale',
      scope: 'new_scope',
    });
  });
});
