import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {screen} from '@testing-library/react';
import {getComplexTableAttribute} from '../../../factories';
import ReferenceEntityProperties from '../../../../src/attribute/ColumDefinitionProperties/ReferenceEntityProperties';

jest.mock('../../../../src/fetchers/ReferenceEntityFetcher');

describe('ReferenceEntityProperties', () => {
  it('should render the component', async () => {
    const tableAttribute = getComplexTableAttribute('reference_entity');
    renderWithProviders(
      <ReferenceEntityProperties
        attribute={tableAttribute}
        selectedColumn={tableAttribute.table_configuration[0]}
        handleChange={jest.fn()}
      />
    );

    expect(screen.getByText('pim_table_attribute.form.attribute.reference_entity')).toBeInTheDocument();
    expect(await screen.findByText('City')).toBeInTheDocument();
  });
});
