import {View} from 'backbone';

type ViewBuilder = {
  build(viewName: string): Promise<View>;
};

export {ViewBuilder};
