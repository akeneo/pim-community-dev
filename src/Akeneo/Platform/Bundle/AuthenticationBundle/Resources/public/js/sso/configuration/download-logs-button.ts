
import BaseForm = require('pimui/js/view/base');

const _ = require('underscore');
const __ = require('oro/translator');
const template = require('pim/template/authentication/sso/configuration/download-logs-button');
const routing = require('routing')

interface Config {
    url: string;
    label: string;
}

class DownloadLogsButton extends BaseForm {
    private readonly template = _.template(template);
    private readonly config: Config;

    public constructor(options: { config: Config }) {
        super(options);

        this.config = {...this.config, ...options.config};
    }

    public render(): BaseForm {
        this.$el.html(this.template({
            url: routing.generate(this.config.url),
            label: __(this.config.label)
        }));

        return this;
    }
}

export = DownloadLogsButton;
