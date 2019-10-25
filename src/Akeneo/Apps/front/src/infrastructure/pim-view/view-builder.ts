import {View} from 'backbone';

export interface ViewBuilder {
    build(viewName: string): Promise<View>;
}
