import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {screen} from '@testing-library/react';
import {getComplexTableAttribute} from '../../../factories';
import {RecordColumnDefinition} from '../../../../src/models';
import RecordProperties from '../../../../src/attribute/ColumDefinitionProperties/RecordProperties';

jest.mock('../../../../src/fetchers/ReferenceEntityFetcher');

describe('RecordProperties', () => {
  it('should render the component', async () => {
    const recordColumn: RecordColumnDefinition = {
      code: 'city_column',
      reference_entity_identifier: 'city',
      validations: {},
      data_type: 'record',
      labels: {},
      is_required_for_completeness: false,
    };
    const tableAttribute = getComplexTableAttribute();
    tableAttribute.table_configuration.push(recordColumn);

    renderWithProviders(
      <RecordProperties attribute={tableAttribute} selectedColumn={recordColumn} handleChange={jest.fn()} />
    );

    expect(screen.getByText('pim_table_attribute.form.attribute.reference_entity')).toBeInTheDocument();
    expect(await screen.findByText('City')).toBeInTheDocument();
  });
});
