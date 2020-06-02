type Attribute = {
  code: string;
  type: string;
  group: string;
  unique: boolean;
  useable_as_grid_filter: boolean;
  allowed_extensions: string[];
  metric_family: string | null;
  default_metric_unit: string | null;
  reference_data_name: string | null;
  available_locales: string[];
  max_characters: number | null;
  validation_rule: any | null;
  validation_regexp: any | null;
  wysiwyg_enabled: boolean | null;
  number_min: number | null;
  number_max: number | null;
  decimals_allowed: boolean | null;
  negative_allowed: boolean | null;
  date_min: string | null;
  date_max: string | null;
  max_file_size: number | null;
  minimum_input_length: number | null;
  sort_order: number;
  localizable: boolean;
  scopable: boolean;
  labels: { [locale: string]: string };
  auto_option_sorting: boolean | null;
  is_read_only: boolean;
  empty_value: any | null;
  field_type: string;
  filter_types: { [type: string]: string };
  is_locale_specific: boolean;
  meta: { id: number } & { [key: string]: any };
};

const validateLocalizableScopableAttribute = (
  attribute: Attribute,
  locale: string | null,
  scope: string | null
): boolean => {
  let isValidated = true;
  if (attribute.localizable && locale === null) {
    console.error(
      `The ${attribute.code} attribute code is localizable but no locale is provided.`
    );
    isValidated = false;
  }

  if (!attribute.localizable && locale !== null) {
    console.error(
      `The ${attribute.code} attribute code is not localizable but a locale is provided.`
    );
    isValidated = false;
  }

  if (attribute.scopable && scope === null) {
    console.error(
      `The ${attribute.code} attribute code is scopable but no scope is provided.`
    );
    isValidated = false;
  }

  if (!attribute.scopable && scope !== null) {
    console.error(
      `The ${attribute.code} attribute code is not scopable but a scope is provided.`
    );
    isValidated = false;
  }

  return isValidated;
};

export { Attribute, validateLocalizableScopableAttribute };
