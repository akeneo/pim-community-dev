export type Attribute = {
    label: string;
    code: string;
    type: string;
    scopable: boolean;
    localizable: boolean;
    measurement_family?: string;
    default_measurement_unit?: string;
};
