import BaseView = require('pimui/js/view/base');
import React from "react";
import ReactDOM from "react-dom";
import Index from 'akeneopimstructure/js/attribute-option/Index';

const __ = require('oro/translator');

class Choices extends BaseView {
    private config: any;

    initialize(config: any): void {
        this.config = config.config;
        BaseView.prototype.initialize.apply(this, arguments);
    }

    configure(): JQueryPromise<any> {
        if (this.isActive()) {
            this.trigger('tab:register', {
                code: this.code,
                label: __(this.config.label)
            });
        }

        return super.configure();
    }

    render(): any {
        if (! this.isActive()) {
            return;
        }

        ReactDOM.render(
            <Index />,
            this.el
        );
        return this;
    }

    private isActive() {
        return this.config.activeForTypes.includes((this.getRoot() as any).getType());
    }
}

export = Choices;
