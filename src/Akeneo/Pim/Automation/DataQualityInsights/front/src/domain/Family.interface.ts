export default interface Family {
  attributes: Attribute[];
  code: string;
  labels: {
    [locale: string]: string;
  };
}

interface AttributeMeta {
  id: number;
}
export interface Attribute {
  code: string;
  type: string;
  group: string;
  validation_rule: string | null,
  validation_regexp: string | null,
  wysiwyg_enabled: boolean | null;
  localizable: boolean;
  scopable: boolean;
  labels: {
    [locale: string]: string;
  }
  is_read_only: boolean;
  meta: AttributeMeta;
}
