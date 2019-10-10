export interface ViewBuilder {
    build(viewName: string): Promise<any>;
}
