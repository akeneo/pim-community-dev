const path = require('path')
const yaml = require('yamljs')
const fs = require('fs')

// To grab and generate
    // fetchers.js - enrich/requirejs.yml:config.pim/fetcher-registry.fetchers
    // controllers.js - enrich/requirejs.yml:config.pim/controller-registry.controllers

// To provide to be imported
    // config.json - enrich/requirejs.yml:config.config
    // paths.json
    // paths.overrides.json
    // navigation.json (including oro menu items and tree, titles)
    // form-extensions (probably use chunks in webpack)
    // savers.json - extract from enrich/requirejs.yml:config.config (all savers and removers)
    // removers.json


// config.js (imports the others)
// needs: defaultController, messages, events
