import {useFetch, useRoute} from '@akeneo-pim-community/shared';
import {Category} from '../../models';

type FormField = {
  value: string;
  fullName: string;
  label: string;
};

type FormChoiceField = FormField & {
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
};

type EditCategoryResponse = {
  category: Category;
  form: EditCategoryForm;
};

const useCategory = (categoryId: number) => {
  const url = useRoute('pim_enrich_categorytree_edit', {
    id: categoryId.toString(),
  });

  const {data, fetch, error, status} = useFetch<EditCategoryResponse>(url);

  return {
    category: data ? data.category : null,
    formData: data ? data.form : null,
    load: fetch,
    status,
    error,
  };
};

export {useCategory, EditCategoryForm};
