export interface Router {
    generate: (route: string, parameters?: {[param: string]: string|undefined}) => string;
    redirect: (fragment: string, options?: object) => void;
}
