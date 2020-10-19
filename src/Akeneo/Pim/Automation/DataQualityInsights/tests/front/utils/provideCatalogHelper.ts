import {Attribute, Family, Product} from "@akeneo-pim-community/data-quality-insights/src/domain";

type Labels = {
    [locale:string]: string;
}
const aProduct = (id: number = 1234, labels: Labels = {}, identifier: string = 'idx_1234', family: string = 'a_family'): Product => {
    return  {
        categories: [],
        enabled: true,
        family,
        identifier,
        created: null,
        updated: null,
        meta: {
            id,
            label: labels,
            level: null,
            attributes_for_this_level: [],
            model_type: "product",
            variant_navigation: [],
            family_variant: {
                variant_attribute_sets: [],
            },
            parent_attributes: [],
        }
    };
};


const aFamily = (code: string, id: number = 1234, labels: Labels = {}, attributes: Attribute[] = [], attribute_as_label: string = '',  ): Family => {
    return {
        attributes,
        code,
        attribute_as_label,
        labels,
        meta: {
            id
        }
    };
};

const anAttribute = (code: string = 'an_attribute', id: number = 1234, type: string = 'a_type', group: string ='an_attribute_group', labels: Labels = {}): Attribute => {
    return {
        code,
        labels,
        type,
        group,
        meta: {
            id
        }
    };
}

export {aProduct, aFamily, anAttribute};