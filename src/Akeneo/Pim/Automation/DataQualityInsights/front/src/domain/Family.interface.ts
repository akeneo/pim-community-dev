export default interface Family {
  attributes: Attribute[];
  code: string;
  labels: {
    [locale: string]: string;
  };
}

interface Attribute {
  code: string;
  labels: {
    [locale: string]: string;
  }
}
