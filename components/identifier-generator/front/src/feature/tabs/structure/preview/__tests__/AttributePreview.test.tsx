import React from 'react';
import {render} from '../../../../tests/test-utils';
import {AbbreviationType, Operator, PROPERTY_NAMES, SimpleSelectProperty} from '../../../../models';
import {waitFor} from '@testing-library/react';
import {AttributePreview} from '../AttributePreview';

describe('AttributePreview', () => {
  it('should display the entire label when process is code', async () => {
    const simpleSelectProperty: SimpleSelectProperty = {
      type: PROPERTY_NAMES.SIMPLE_SELECT,
      attributeCode: 'simple_select',
      process: {
        type: AbbreviationType.NO,
      },
    };
    const screen = render(<AttributePreview property={simpleSelectProperty} />);

    await waitFor(() => {
      expect(screen.getByText('simple_select')).toBeInTheDocument();
    });
  });

  it('should display the label truncated when process is truncate', async () => {
    const simpleSelectProperty: SimpleSelectProperty = {
      type: PROPERTY_NAMES.SIMPLE_SELECT,
      attributeCode: 'simple_select',
      process: {
        type: AbbreviationType.TRUNCATE,
        operator: Operator.EQUALS,
        value: 3,
      },
    };
    const screen = render(<AttributePreview property={simpleSelectProperty} />);

    await waitFor(() => {
      expect(screen.getByText('sim')).toBeInTheDocument();
    });
  });
});
