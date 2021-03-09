export interface Router {
    generate: (route: string, parameters?: {[param: string]: string|null}) => string;
    redirect: (fragment: string, options?: object) => void;
}
