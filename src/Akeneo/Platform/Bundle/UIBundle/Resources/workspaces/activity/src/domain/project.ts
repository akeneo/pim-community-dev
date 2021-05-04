type ProjectCompletenessType = {
  is_completeness_computed: boolean; //only on fetchCompleteness call...
  is_complete: boolean;
  products_count_done: number;
  products_count_in_progress: number;
  products_count_todo: number;
  ratio_done: number;
  ratio_in_progress: number;
  ratio_todo: number;
};

type Project = {
  code: string;
  label: string;
  channel: {
    code: string;
    labels: {
      [code: string]: string;
    };
  };
  locale: {
    code: string;
    label: string;
  };
  completeness: {
    ratio_done: number;
  };
  due_date: string;
  description: string;
  owner: {
    username: string;
  };
};

export {Project, ProjectCompletenessType};
