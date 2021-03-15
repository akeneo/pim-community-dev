export interface Router {
    generate: (route: string, parameters?: {[param: string]: string}) => string;
    redirect: (fragment: string, options?: object) => void;
}
