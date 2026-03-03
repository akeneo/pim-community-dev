import {FC, StrictMode} from 'react';
import {QueryClient, QueryClientProvider} from 'react-query';
import {Route, HashRouter as Router, Switch} from 'react-router-dom';
import {ErrorBoundary} from './ErrorBoundary';
import {CanLeavePageProvider, EditCategoryProvider} from './components';
import {SaveStatusProvider} from './components/providers/SaveStatusProvider';
import {TemplateFormProvider} from './components/providers/TemplateFormProvider';
import {UnsavedChangesGuard} from './components/templates/UnsavedChangeGuard';
import {CategoriesIndex, CategoriesTreePage, CategoryEditPage, TemplatePage} from './pages';
import {BadRequestError} from './tools/apiFetch';

const useErrorBoundary = (error: unknown) => false === error instanceof BadRequestError;

type Props = {
  setCanLeavePage: (canLeavePage: boolean) => void;
  setLeavePageMessage: (leavePageMessage: string) => void;
};

const CategoriesApp: FC<Props> = ({setCanLeavePage, setLeavePageMessage}) => {
  const queryClient = new QueryClient({
    defaultOptions: {
      queries: {useErrorBoundary},
      mutations: {useErrorBoundary},
    },
  });

  return (
    <StrictMode>
      <ErrorBoundary>
        <QueryClientProvider client={queryClient}>
          <CanLeavePageProvider setCanLeavePage={setCanLeavePage} setLeavePageMessage={setLeavePageMessage}>
            <Router basename="/enrich/product-category-tree">
              <Switch>
                <Route path="/:treeId/tree">
                  <CategoriesTreePage />
                </Route>
                <Route path="/:categoryId/edit">
                  <EditCategoryProvider>
                    <CategoryEditPage />
                  </EditCategoryProvider>
                </Route>
                <Route path="/:treeId/template/:templateId">
                  <SaveStatusProvider>
                    <UnsavedChangesGuard />
                    <TemplateFormProvider>
                      <TemplatePage />
                    </TemplateFormProvider>
                  </SaveStatusProvider>
                </Route>
                <Route path="/">
                  <CategoriesIndex />
                </Route>
              </Switch>
            </Router>
          </CanLeavePageProvider>
        </QueryClientProvider>
      </ErrorBoundary>
    </StrictMode>
  );
};

export {CategoriesApp};
