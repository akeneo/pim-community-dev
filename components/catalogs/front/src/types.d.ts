declare module 'pimui/js/translator' {
    function translate(id: string, placeholders?: {[name: string]: string | number}, count?: number): string;
    export = translate;
}
