export interface FeatureFlags {
    isEnabled: (feature: string) => boolean;
}
