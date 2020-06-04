type CategoryCode = string;

type Category = {
  code: CategoryCode;
  parent: CategoryCode;
  labels: { [locale: string]: string };
  id: number;
};

export { CategoryCode, Category };
