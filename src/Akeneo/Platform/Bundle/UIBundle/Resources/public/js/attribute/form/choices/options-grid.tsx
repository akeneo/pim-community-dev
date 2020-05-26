import BaseView = require('pimui/js/view/base');
import ReactDOM from 'react-dom';
import React from "react";
import OptionsGrid from './OptionsGrid';
import DQITest from './DQITest';
const FetcherRegistry = require('pim/fetcher-registry');
const Router = require('pim/router');

class OptionsGridLegacy extends BaseView {
    isRendered = false;

    render(): BaseView {
        const attributeId = this.getFormData().meta.id;
        FetcherRegistry.getFetcher('locale').fetchActivated().then((locales: any) => {
            $.ajax({
                url: Router.generate('pim_enrich_attributeoption_index',{attributeId: attributeId}),
                type: 'GET',
            }).then(response => {
                if (!this.isRendered) {
                    ReactDOM.render(
                        <>
                            <DQITest />
                            <OptionsGrid locales={locales} options={response}/>
                        </>,
                        this.el
                    );
                    this.isRendered = true;
                }
            });
        })
        return this;
    }

    remove() {
        ReactDOM.unmountComponentAtNode(this.el);
        return super.remove();
    }
}

export = OptionsGridLegacy;
