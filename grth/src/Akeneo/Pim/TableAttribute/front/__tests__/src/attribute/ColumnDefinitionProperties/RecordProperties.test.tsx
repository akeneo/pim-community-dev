import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {screen} from '@testing-library/react';
import {getComplexTableAttribute} from '../../../factories';
import RecordProperties from '../../../../src/attribute/ColumDefinitionProperties/RecordProperties';

jest.mock('../../../../src/fetchers/ReferenceEntityFetcher');

describe('RecordProperties', () => {
  it('should render the component', async () => {
    const tableAttribute = getComplexTableAttribute('record');
    renderWithProviders(
      <RecordProperties
        attribute={tableAttribute}
        selectedColumn={tableAttribute.table_configuration[0]}
        handleChange={jest.fn()}
      />
    );

    expect(screen.getByText('pim_table_attribute.form.attribute.reference_entity')).toBeInTheDocument();
    expect(await screen.findByText('City')).toBeInTheDocument();
  });
});
