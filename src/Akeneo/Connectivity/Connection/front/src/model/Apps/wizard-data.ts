import ScopeMessage from './scope-message';

export interface AppWizardData {
    appName: string;
    appLogo: string;
    appUrl: string | null;
    scopeMessages: ScopeMessage[];
    oldScopeMessages: ScopeMessage[] | null;
    authenticationScopes: Array<'email' | 'profile'>;
}
