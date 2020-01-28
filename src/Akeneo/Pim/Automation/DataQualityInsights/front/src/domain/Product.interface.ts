export default interface Product {
  categories: string[];
  enabled: boolean;
  family: string | null;
  identifier: string | null;
  meta: Meta;
  created: string|null;
  updated: string|null;
}

interface Meta {
  id: number | null;
  label: {
    [locale: string]: string;
  };
}
