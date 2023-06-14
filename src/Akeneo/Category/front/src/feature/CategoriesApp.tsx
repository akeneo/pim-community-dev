import {FC} from 'react';
import {QueryClient, QueryClientProvider} from 'react-query';
import {Route, HashRouter as Router, Switch} from 'react-router-dom';
import {CanLeavePageProvider, EditCategoryProvider} from './components';
import {TemplateFormProvider} from './components/providers/TemplateFormProvider';
import {CategoriesIndex, CategoriesTreePage, CategoryEditPage, TemplatePage} from './pages';

type Props = {
  setCanLeavePage: (canLeavePage: boolean) => void;
  setLeavePageMessage: (leavePageMessage: string) => void;
};

const CategoriesApp: FC<Props> = ({setCanLeavePage, setLeavePageMessage}) => {
  const queryClient = new QueryClient();

  return (
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
              <TemplateFormProvider>
                <TemplatePage />
              </TemplateFormProvider>
            </Route>
            <Route path="/">
              <CategoriesIndex />
            </Route>
          </Switch>
        </Router>
      </CanLeavePageProvider>
    </QueryClientProvider>
  );
};

export {CategoriesApp};
