export type Attribute = {
    label: string;
    code: string;
    type: string;
    scopable: boolean;
    localizable: boolean;
    attribute_group_code: string;
    attribute_group_label: string;
    measurement_family?: string;
    default_measurement_unit?: string;
    asset_family?: string;
    reference_entity?: string;
};
