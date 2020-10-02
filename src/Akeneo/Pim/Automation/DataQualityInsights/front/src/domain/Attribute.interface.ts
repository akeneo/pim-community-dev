export default interface Attribute {
  code: string;
  type: string;
  group: string;
  validation_rule?: string | null,
  validation_regexp?: string | null,
  wysiwyg_enabled?: boolean | null;
  localizable?: boolean;
  scopable?: boolean;
  labels?: {
    [locale: string]: string;
  }
  is_read_only?: boolean;
  sort_order?: number;
  meta: AttributeMeta;
}

interface AttributeMeta {
  id: number;
}
