export interface Router {
    generate: (route: string, parameters?: object) => string;
    redirect: (fragment: string, options?: object) => void;
}
