import * as JQuery from 'jquery';
const Routing = require('routing');

export function isCloud(): JQueryPromise<any> {
    return JQuery.get(Routing.generate('pim_system_edition')).then((response: any) => {
        return response.isCloudEdition;
    });
}
