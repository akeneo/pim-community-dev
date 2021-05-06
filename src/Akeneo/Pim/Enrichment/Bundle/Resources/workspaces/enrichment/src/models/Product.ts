type VariantNavigation = {
  axes: {
    [locale: string]: string;
  };
  selected: {
    id: number;
  };
};

type Product = {
  meta: {
    level: null | number;
    attributes_for_this_level: string[];
    model_type: 'product' | 'product_model';
    variant_navigation: VariantNavigation[];
    parent_attributes: string[];
    family_variant: string;
  };
};

export {Product};
