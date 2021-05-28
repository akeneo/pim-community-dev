import {useFetch, useRoute} from '@akeneo-pim-community/shared';
import {Category} from '../../models';

type FormField = {
  value: string;
  fullName: string;
  label: string;
};

type FormChoiceField = FormField & {
  value: string[];
  choices: {
    value: string;
    label: string;
  }[];
};

// @todo move to models?
type EditCategoryForm = {
  label: {[locale: string]: FormField};
  _token: FormField;
  permissions?: {
    view: FormChoiceField;
    edit: FormChoiceField;
    own: FormChoiceField;
    apply_on_children: FormField;
  };
  errors: string[];
};

type EditCategoryData = {
  category: Category;
  form: EditCategoryForm;
};

const useCategory = (categoryId: number) => {
  const url = useRoute('pim_enrich_categorytree_edit', {
    id: categoryId.toString(),
  });

  const {data, fetch, error, status} = useFetch<EditCategoryData>(url);

  return {
    categoryData: data,
    load: fetch,
    status,
    error,
  };
};

export {useCategory, EditCategoryForm};
