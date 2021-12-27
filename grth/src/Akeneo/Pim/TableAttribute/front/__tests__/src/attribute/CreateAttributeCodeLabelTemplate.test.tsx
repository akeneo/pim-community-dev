import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {act, fireEvent, screen} from '@testing-library/react';
import {view} from '../../../src';
import 'jest-fetch-mock';

const CreateAttributeCodeLabelTemplate = view;

describe('CreateAttributeCodeLabelTemplate', () => {
  afterEach(() => {
    fetchMock.resetMocks();
  });
  it('should render the component', async () => {
    fetchMock.mockResponses([JSON.stringify([]), {status: 200}]);

    renderWithProviders(
      <CreateAttributeCodeLabelTemplate
        onStepConfirm={jest.fn()}
        onClose={jest.fn()}
        initialData={{template: 'empty_table'}}
      />
    );

    expect(await screen.findByText('pim_common.create')).toBeInTheDocument();
  });

  it('should callback confirm with selection of template variation', async () => {
    fetchMock.mockResponses([JSON.stringify([]), {status: 200}], [JSON.stringify([]), {status: 200}]);

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
    fireEvent.click(screen.getByTitle('pim_common.open'));
    expect(await screen.findByText('pim_table_attribute.templates.nutrition-europe')).toBeInTheDocument();
    fireEvent.click(screen.getByText('pim_table_attribute.templates.nutrition-europe'));
    fireEvent.click(screen.getByText('pim_common.confirm'));

    expect(handleStepConfirm).toBeCalledWith({
      code: 'A_new_attribute',
      label: 'A new attribute',
      template_variation: 'nutrition-europe',
    });
  });

  it('should add a violation when code is already used', async () => {
    fetchMock.mockResponses([JSON.stringify([]), {status: 200}], [JSON.stringify([{code: 'name'}]), {status: 200}]);

    const handleStepConfirm = jest.fn();
    renderWithProviders(
      <CreateAttributeCodeLabelTemplate
        onStepConfirm={handleStepConfirm}
        onClose={jest.fn()}
        initialData={{template: 'empty_table'}}
      />
    );

    expect(await screen.findByText('pim_common.create')).toBeInTheDocument();
    act(() => {
      fireEvent.change(screen.getByLabelText(/pim_common.code/), {target: {value: 'name'}});
    });
    expect(await screen.findByText('pim_enrich.entity.attribute.property.code.is_duplicate')).toBeInTheDocument();
  });
});
