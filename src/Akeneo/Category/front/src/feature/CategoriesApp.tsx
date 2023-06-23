import {FC} from 'react';
import {QueryClient, QueryClientProvider} from 'react-query';
import {Route, HashRouter as Router, Switch} from 'react-router-dom';
import {CanLeavePageProvider, EditCategoryProvider} from './components';
import {UnsavedChangesGuard} from './components/templates/UnsavedChangeGuard';
import {SaveStatusProvider} from './components/providers/SaveStatusProvider';
import {TemplateFormProvider} from './components/providers/TemplateFormProvider';
import {CategoriesIndex, CategoriesTreePage, CategoryEditPage, TemplatePage} from './pages';
import {BadRequestError} from './tools/apiFetch';
import {ErrorBoundary} from './ErrorBoundary';

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
  );
};

export {CategoriesApp};
