import {EventsHash} from 'backbone';

const _ = require('underscore');
const BaseText = require('pim/form/common/fields/text');
const template = require('pim/template/form/common/fields/copiable-text');

class CopiableTextField extends BaseText {
    readonly template = _.template(template);

    public events(): EventsHash {
        return {
            'click .copy': (e) => {

                const target = <HTMLElement> e.currentTarget;
                const container = <HTMLElement> target.parentElement;

                if (null !== container){
                    const input = container.getElementsByTagName('input')[0];
                    input.select();

                    document.execCommand('copy');
                }
            },
        };
    }
}

export = CopiableTextField;
