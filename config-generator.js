const path = require('path')
const yaml = require('yamljs')
const fs = require('fs')
const _ = require('lodash')

const bundleDirectory = './src/Pim/Bundle'
const requirePath = _.template(`${bundleDirectory}/<%=bundleName%>/Resources/config/requirejs.yml`)

const configOutputs = {
    fetchers: {
        inputPath: `config.config['pim/fetcher-registry'].fetchers`,
        outputType: 'module',
        outputPath: './web/js/config/fetchers.json'
    },
    paths: {
        inputPath: 'config.paths',
        outputType: 'json',
        outputPath: './web/js/config/paths.json'
    },
}


const getBundleNames = () => {
    return fs.readdirSync(bundleDirectory, 'utf8')
}

const getParsedFile = (fileName) => {
    try {
        const resolvedPath = path.resolve(__dirname, fileName)
        return yaml.parse(fs.readFileSync(resolvedPath, 'utf8'))
    } catch(e) {
        // console.info(e, 'Error in getParsedFile')
        return {}
    }
}

const getConfigFiles = () => {
    const bundles = getBundleNames()
    const bundleConfigs = {}

    _.each(bundles, (bundleName) => {
        const requireFilePath = requirePath({ bundleName })
        bundleConfigs[bundleName] = getParsedFile(requireFilePath)
    })

    return bundleConfigs
}

const extractConfig = () => {

}

const createModuleWithContents = (name, contents) => {

}

const createJSONWithContents = (name, contents) => {

}

const configFiles = getConfigFiles()

const files = _.map(configOutputs, (output) => {
    const contents = {}
    _.each(configFiles, (file) => {
        const props = _.get(file, output.inputPath) || {}
        _.each(props, (prop, name) => {
            contents[name] = prop
        })
    })
    return { [output.outputPath]: contents };
})

console.log(files)
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
