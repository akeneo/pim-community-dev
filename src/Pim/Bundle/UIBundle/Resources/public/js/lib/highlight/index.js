define(['highlight', 'highligh/languages/xml'], function (hljs, xml) {
    hljs.registerLanguage('xml', xml);

    return hljs;
});
