import React from 'react';
import {act, screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared/lib/tests';
import {AddTemplateAttributeModal} from './AddTemplateAttributeModal';
import {QueryClient, QueryClientProvider} from 'react-query';
import userEvent from '@testing-library/user-event';

const queryClient = new QueryClient();

test('It sends the form with new attribute code', async () => {
  const jestSpy = jest.spyOn(window, 'fetch').mockResolvedValueOnce(new Response('{}'));

  renderWithProviders(
    <QueryClientProvider client={queryClient}>
      <AddTemplateAttributeModal templateId={'73962f60-e216-4046-82d8-cd06110207cc'} onClose={jest.fn()} />
    </QueryClientProvider>
  );

  userEvent.type(screen.getByLabelText(/pim_common.code/), 'new_attribute');

  await act(async () => {
    await userEvent.click(screen.getByText('akeneo.category.template.add_attribute.confirmation_modal.create'));
  });

  expect(jestSpy).toHaveBeenCalledWith('pim_category_template_rest_add_attribute', {
    method: 'POST',
    body: '{"code":"new_attribute","label":"","type":"text","is_localizable":false,"is_scopable":false}',
    headers: {
      'Content-Type': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
    },
  });
});
