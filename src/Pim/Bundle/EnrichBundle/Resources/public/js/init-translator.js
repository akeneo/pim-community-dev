

import $ from 'jquery'
import UserContext from 'pim/user-context'
import Translator from 'translator-lib'
export default {
    fetch: function () {
        return $.getJSON('js/translation/' + UserContext.get('uiLocale').split('_')[0] + '.js')
                    .then(function (messages) {
                        Translator.fromJSON(messages)
                    })
    }
}

