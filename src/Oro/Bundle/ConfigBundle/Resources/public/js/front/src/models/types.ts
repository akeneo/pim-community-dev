 export interface Locale {
    id: number;
    code: string;
    label: string;
    region: string;
    language: string;
}

export interface ConfigServicePayload {
    "pim_ui___language": ScopedValue;
    "pim_analytics___version_update": ScopedValue;
    "pim_ui___loading_message_enabled": ScopedValue
    "pim_ui___loading_messages": ScopedValue;
}

export interface ScopedValue {
    value: string | null;
    scope: "app";
    use_parent_scope_value: boolean;
}
