export interface ScopedValue<ValueType> {
  value: ValueType;
  scope: 'app';
  use_parent_scope_value: boolean;
}

type BooleanBackend = string | boolean | null;

/**
 * System Configuration in backend
 * For boolean values we can have :
 *  - true represented by true or "1"
 *  - false represented by false or "0" or even null
 */
export interface ConfigServicePayloadBackend {
  pim_ui___language: ScopedValue<string>;
  pim_analytics___version_update: ScopedValue<BooleanBackend>;
  pim_ui___loading_message_enabled: ScopedValue<BooleanBackend>;
  pim_ui___loading_messages: ScopedValue<string>;
  pim_ui___sandbox_banner: ScopedValue<BooleanBackend>;
}

/**
 * System Configuration in frontend
 * boolean values are represented by … booleans
 */
export interface ConfigServicePayloadFrontend {
  pim_ui___language: ScopedValue<string>;
  pim_analytics___version_update: ScopedValue<boolean>;
  pim_ui___loading_message_enabled: ScopedValue<boolean>;
  pim_ui___loading_messages: ScopedValue<string>;
  pim_ui___sandbox_banner: ScopedValue<boolean>;
}

function scopedValueBoolFrontToBack(sv: ScopedValue<boolean>): ScopedValue<string> {
  return {
    ...sv,
    value: sv.value ? '1' : '0',
  };
}

function scopedValueBoolBackToFront(sv: ScopedValue<BooleanBackend>): ScopedValue<boolean> {
  return {
    ...sv,
    value: sv.value === true || sv.value === '1',
  };
}

export function configFrontToBack(config: ConfigServicePayloadFrontend): ConfigServicePayloadBackend {
  return {
    ...config,
    pim_analytics___version_update: scopedValueBoolFrontToBack(config.pim_analytics___version_update),
    pim_ui___loading_message_enabled: scopedValueBoolFrontToBack(config.pim_ui___loading_message_enabled),
    pim_ui___sandbox_banner: scopedValueBoolFrontToBack(config.pim_ui___sandbox_banner),
  };
}

export function configBackToFront(config: ConfigServicePayloadBackend): ConfigServicePayloadFrontend {
  return {
    ...config,
    pim_analytics___version_update: scopedValueBoolBackToFront(config.pim_analytics___version_update),
    pim_ui___loading_message_enabled: scopedValueBoolBackToFront(config.pim_ui___loading_message_enabled),
    pim_ui___sandbox_banner: scopedValueBoolBackToFront(config.pim_ui___sandbox_banner),
  };
}
