interface ViewBuilder {
    build(viewName: string): Promise<any>;
}

export const viewBuilder: ViewBuilder = require('pim/form-builder');
