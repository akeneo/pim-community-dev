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
  level: null | number;
  attributes_for_this_level: string[];
  model_type: "product" | "product_model";
  variant_navigation: VariantNavigation[];
  family_variant: {
    variant_attribute_sets: VariantAttributeSet[]
  };
  parent_attributes: string[],
}

interface VariantNavigation {
  axes: {
    [locale: string]: string;
  };
  selected: {
    id: number;
  };
}

interface VariantAttributeSet {
  attributes: string[];
}
