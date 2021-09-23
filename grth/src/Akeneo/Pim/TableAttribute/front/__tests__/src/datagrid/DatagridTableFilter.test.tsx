import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {defaultFilterValuesMapping} from "../../factories";
import {DatagridTableFilter} from "../../../src/datagrid";

jest.mock('../../../src/fetchers/AttributeFetcher');

describe('DatagridTableFilter', () => {
  it('should display a filter', () => {
    renderWithProviders(<DatagridTableFilter
      onChange={jest.fn()}
      showLabel={true}
      label={'Nutrition'}
      attributeCode={'nutrition'}
      canDisable={true}
      onDisable={jest.fn()}
      filterValuesMapping={defaultFilterValuesMapping}
    />);

    // TODO Continue
  });
});
