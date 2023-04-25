import React from 'react';
import {act, screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared/lib/tests';
import {QueryClient, QueryClientProvider} from 'react-query';
import userEvent from '@testing-library/user-event';
import {Attribute, CategoryAttributeType} from '../../models';
import {AttributeList} from './AttributeList';

const queryClient = new QueryClient();

test('It open the add attribute modal when clicking on add attribute button', async () => {
  jest.spyOn(window, 'fetch').mockResolvedValueOnce(new Response('{}'));

  const attributes: Attribute[] = [
    {
      uuid: 'attribute_uuid',
      code: 'attribute_1',
      type: 'text',
      order: 1,
      is_scopable: false,
      is_localizable: false,
      labels: {
        en_US: 'Attribute 1',
      },
      template_uuid: 'template_uuid',
    },
  ];

  renderWithProviders(
    <QueryClientProvider client={queryClient}>
      <AttributeList
        attributes={attributes}
        selectedAttribute={attributes[0]}
        templateId={'template_uuid'}
        onAttributeSelection={() => {}}
      />
    </QueryClientProvider>
  );

  // check the template edit page is open and click on add attribute button
  await act(async () => {
    expect(screen.getByText('akeneo.category.attributes')).toBeInTheDocument();
    await userEvent.click(screen.getByText('akeneo.category.template.add_attribute.add_button'));
  });

  // check the modal is open and type attribute code in code field
  expect(screen.getByLabelText(/pim_common.code/)).toBeInTheDocument();
  userEvent.type(screen.getByLabelText(/pim_common.code/), 'new_attribute');

  // click on create button in modal
  await act(async () => {
    await userEvent.click(screen.getByText('akeneo.category.template.add_attribute.confirmation_modal.create'));
  });

  // modale is closed and edit template page is visible
  expect(screen.getByText('akeneo.category.attributes')).toBeInTheDocument();
});
