import React from 'react';
import {act, screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared/lib/tests';
import {DeactivateTemplateAttributeModal} from './DeactivateTemplateAttributeModal';
import {QueryClient, QueryClientProvider} from 'react-query';
import userEvent from '@testing-library/user-event';

const queryClient = new QueryClient();

test('It sends the templateUuid and AttributeUuid for attribute deletion', async () => {
  const jestSpy = jest.spyOn(window, 'fetch').mockResolvedValueOnce(new Response('{}'));

  renderWithProviders(
    <QueryClientProvider client={queryClient}>
      <DeactivateTemplateAttributeModal
        templateUuid={'3f501763-8c7b-4dad-bd01-b0b827233d7e'}
        attribute={{uuid: '4f447ed9-b0af-49ff-b4ce-6b1e06c1aa83', label: 'attributeLabel'}}
        onClose={jest.fn()}
      />
    </QueryClientProvider>
  );

  await act(async () => {
    await userEvent.click(screen.getByText('pim_common.delete'));
  });

  expect(jestSpy).toHaveBeenCalledWith('pim_category_template_rest_delete_attribute', {
    method: 'DELETE',
    headers: {
      'Content-Type': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
    },
  });
});
