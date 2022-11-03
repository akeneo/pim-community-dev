import BaseView = require('pimui/js/view/base');

import {Liveblocks} from "./Liveblocks";

class LiveblocksView extends BaseView {
    render(): BaseView {
        this.renderReact(Liveblocks, {product: this.getFormData()}, this.el)
        return this;
    }
}

export = LiveblocksView;
