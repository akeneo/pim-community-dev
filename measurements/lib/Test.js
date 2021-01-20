import React from 'react';
import { useTranslate } from '@akeneo-pim-community/legacy';
var Test = function () {
    var translate = useTranslate();
    return React.createElement("div", null,
        "That's so awesome \uD83C\uDF89 ",
        translate('pim_common.close'));
};
export default Test;
//# sourceMappingURL=Test.js.map