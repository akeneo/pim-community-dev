export default interface Product {
  categories: string[];
  enabled: boolean;
  family: string | null;
  identifier: string | null;
  meta: Meta;
}

interface Meta {
  id: number | null;
  label: {
    [locale: string]: string;
  };
}
