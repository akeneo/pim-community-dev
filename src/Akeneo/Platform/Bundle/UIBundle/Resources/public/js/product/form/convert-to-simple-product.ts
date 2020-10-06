import {EventsHash} from 'backbone';
import * as _ from 'underscore';

import BaseView = require('pimui/js/view/base');
const Dialog = require('pim/dialog');
const __ = require('oro/translator');
const messenger = require('oro/messenger');
const Routing = require('routing');
const router = require('pim/router');

const template = require('pim/template/product/convert-to-simple-product');

interface Config {
    url: string;
}

class ConvertToSimpleProduct extends BaseView {
    private readonly template = _.template(template);

    private readonly config: Config;

    constructor(options: { config: Config }) {
        super({...options, ...{className: 'AknDropdown-menuLink', tagName: 'button'}});

        this.config = {...this.config, ...options.config};
    }

    /**
     * {@inheritdoc}
     */
    public events(): EventsHash {
        return {
            'click': this.convert,
        };
    }

    public render(): BaseView {
        const formData = this.getFormData();
        if ('product' === formData.meta.model_type && null !== formData.parent) {
            this.$el.html(this.template({
                label: __('pim_enrich.entity.product.module.convert_variant_to_simple.label'),
            }));
        }

        return BaseView.prototype.render.apply(this, arguments);
    }

    private convert() {
        return Dialog.confirm(
            'pim_enrich.entity.product.module.convert_variant_to_simple.dialog.content',
            'pim_enrich.entity.product.module.convert_variant_to_simple.dialog.title',
            () => {
                router.showLoadingMask();

                fetch(Routing.generate(this.config.url, {
                    id: this.getFormData().meta.id,
                }), {
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    method: 'POST',
                })
                    .then((response) => {
                        if (response.ok) {
                            messenger.notify('success', __('pim_enrich.entity.product.flash.update.success'));
                        } else {
                            messenger.notify('error', __('pim_enrich.entity.product.flash.update.fail'));
                        }
                    })
                    .catch((e) => {
                        console.log(e);
                    })
                    .finally(() => {
                        router.hideLoadingMask();
                        router.reloadPage();
                    });
            },
            null,
            'family-variants',
            'pim_common.confirm',
            'pim_common.cancel'
        )
    }
}

export = ConvertToSimpleProduct;
