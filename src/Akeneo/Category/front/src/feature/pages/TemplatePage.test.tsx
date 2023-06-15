import React from 'react';
import {render, screen} from '@testing-library/react';
import {MemoryRouter, Route} from 'react-router-dom';
import {TemplatePage} from './TemplatePage';
import {SaveStatusProvider} from '../components/providers/SaveStatusProvider';
import {UnsavedChangesGuard} from '../components/templates/UnsavedChangeGuard';
import {TemplateFormProvider} from '../components/providers/TemplateFormProvider';
import {CategoryProviders} from 'tests/CategoryProviders';

test('renders the page', () => {
  const treeId = '1';
  const templateId = '2';

  render(
    <CategoryProviders>
      <MemoryRouter initialEntries={[`/${treeId}/template/${templateId}`]}>
        <Route path="/:treeId/template/:templateId">
          <SaveStatusProvider>
            <UnsavedChangesGuard />
            <TemplateFormProvider>
              <TemplatePage />
            </TemplateFormProvider>
          </SaveStatusProvider>
        </Route>
      </MemoryRouter>
    </CategoryProviders>
  );
  const templateElement = screen.getByText(`Template ${templateId}`);
  expect(templateElement).toBeInTheDocument();
});

// it('renders the save status indicator', () => {
//   render(<TemplatePage />);
//   expect(screen.getByTestId('save-status-indicator')).toBeInTheDocument();
// });

// it('renders the template other actions', () => {
//   render(<TemplatePage />);
//   expect(screen.getByTestId('template-other-actions')).toBeInTheDocument();
// });

// it('renders the attribute tab by default', () => {
//   render(<TemplatePage />);
//   expect(screen.getByRole('tab', {name: 'Attributes'})).toHaveAttribute('aria-selected', 'true');
// });

// it('switches to the property tab when clicked', () => {
//   render(<TemplatePage />);
//   const propertyTab = screen.getByRole('tab', {name: 'Properties'});
//   expect(propertyTab).toHaveAttribute('aria-selected', 'false');
//   userEvent.click(propertyTab);
//   expect(propertyTab).toHaveAttribute('aria-selected', 'true');
// });
