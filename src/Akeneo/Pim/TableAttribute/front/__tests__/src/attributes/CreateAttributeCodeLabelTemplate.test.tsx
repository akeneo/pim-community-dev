import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {fireEvent, screen} from '@testing-library/react';
import {view} from '../../../src/attribute/CreateAttributeCodeLabelTemplate';
const CreateAttributeCodeLabelTemplate = view;

describe('CreateAttributeCodeLabelTemplate', () => {
  it('should render the component', () => {
    renderWithProviders(
      <CreateAttributeCodeLabelTemplate
        onStepConfirm={jest.fn()}
        onClose={jest.fn()}
        initialData={{template: 'empty_table'}}
      />
    );

    expect(screen.getByText('pim_common.create')).toBeInTheDocument();
  });

  it('should callback confirm with selection of template variation', async () => {
    const handleStepConfirm = jest.fn();
    renderWithProviders(
      <CreateAttributeCodeLabelTemplate
        onStepConfirm={handleStepConfirm}
        onClose={jest.fn()}
        initialData={{template: 'nutrition'}}
      />
    );

    expect(await screen.findByText('pim_common.create')).toBeInTheDocument();
    fireEvent.change(screen.getByLabelText('pim_common.label'), {target: {value: 'A new attribute'}});
    fireEvent.focus(screen.getAllByRole('textbox')[2]);
    expect(await screen.findByText('nutrition-eu')).toBeInTheDocument();
    fireEvent.click(screen.getByText('nutrition-eu'));
    fireEvent.click(screen.getByText('pim_common.confirm'));

    expect(handleStepConfirm).toBeCalledWith({
      code: 'A_new_attribute',
      label: 'A new attribute',
      template_variation: 'nutrition-eu',
    });
  });
});
