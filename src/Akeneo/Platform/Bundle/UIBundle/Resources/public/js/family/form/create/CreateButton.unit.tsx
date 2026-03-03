import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {CreateButton} from './CreateButton';
import 'jest-fetch-mock';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';

test('it renders the button and opens the dropdown', () => {
  renderWithProviders(<CreateButton />);

  expect(screen.queryByText('pim_enrich.entity.family.module.create.from_scratch')).not.toBeInTheDocument();
  expect(screen.queryByText('pim_enrich.entity.family.module.create.browse_templates')).not.toBeInTheDocument();

  userEvent.click(screen.getByText('pim_enrich.entity.family.module.create.button'));

  expect(screen.queryByText('pim_enrich.entity.family.module.create.from_scratch')).toBeInTheDocument();
  expect(screen.queryByText('pim_enrich.entity.family.module.create.browse_templates')).toBeInTheDocument();
});

test('it opens the dropdown & opens create form', () => {
  renderWithProviders(<CreateButton />);

  expect(screen.queryByText('pim_menu.item.family')).not.toBeInTheDocument();

  userEvent.click(screen.getByText('pim_enrich.entity.family.module.create.button'));
  userEvent.click(screen.getByText('pim_enrich.entity.family.module.create.from_scratch'));

  expect(screen.queryByText('pim_menu.item.family')).toBeInTheDocument();

  expect(screen.queryByText('pim_common.save')).toBeInTheDocument();
});

test('it opens the dropdown & opens templates selector', () => {
  renderWithProviders(<CreateButton />);

  expect(screen.queryByText('pim_menu.item.family')).not.toBeInTheDocument();

  userEvent.click(screen.getByText('pim_enrich.entity.family.module.create.button'));
  userEvent.click(screen.getByText('pim_enrich.entity.family.module.create.browse_templates'));

  expect(document.getElementById('template-selector')).toBeInTheDocument();
});
