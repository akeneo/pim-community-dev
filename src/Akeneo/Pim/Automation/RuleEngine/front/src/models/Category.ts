type CategoryCode = string;
type CategoryId = number;

type Category = {
  code: CategoryCode;
  parent: CategoryCode;
  labels: {[locale: string]: string};
  id: CategoryId;
  root: CategoryId;
};

export {CategoryId, CategoryCode, Category};
