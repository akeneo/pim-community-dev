import ScopeMessage from './scope-message';

export interface AppWizardData {
    appName: string;
    appLogo: string;
    appUrl: string | null;
    scopeMessages: ScopeMessage[];
    authenticationScopes: Array<'email' | 'profile'>;
}
