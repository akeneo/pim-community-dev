import ScopeMessage from './scope-message';

export interface AppWizardData {
    appName: string;
    appLogo: string;
    appUrl: string | null;
    appIsCertified: boolean;
    scopeMessages: ScopeMessage[];
    oldScopeMessages: ScopeMessage[] | null;
    authenticationScopes: Array<'email' | 'profile'>;
    oldAuthenticationScopes: Array<'email' | 'profile'> | null;
    displayCheckboxConsent: boolean;
}
