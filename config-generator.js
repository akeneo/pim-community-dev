const path = require('path')
const yaml = require('yamljs')
const fs = require('fs')
const _ = require('lodash')
const pascalCase = require('pascal-case')

const bundleDirectory = './src/Pim/Bundle'
const requirePath = _.template(`${bundleDirectory}/<%=bundleName%>/Resources/config/requirejs.yml`)

const moduleOutputs = {
    fetchers: {
        inputPath: `config.config['pim/fetcher-registry'].fetchers`,
        outputPath: `${bundleDirectory}/EnrichBundle/Resources/public/js/config/fetchers.js`,
        formatter: (contents, path) => {
            return {
                path,
                exports: _.uniq(_.compact(_.map(contents[path], 'module')))
            }
        }
    },
}

const JSONOutputs = {
    paths: {
        inputPath: 'config.paths',
        outputType: 'json',
        outputPath: './web/js/config/paths.json'
    }
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

const generateJSONs = (name, contents) => {

}

const configFiles = getConfigFiles()

const getModuleOutputs = () => {
    return _.map(moduleOutputs, (output) => {
        const contents = {}
        _.each(configFiles, (file) => {
            const props = _.get(file, output.inputPath) || {}
            _.each(props, (prop, name) => {
                contents[name] = prop
            })
        })

        const fileContents = { [output.outputPath]: contents }
        if (output.formatter) return output.formatter(fileContents, output.outputPath)
        return fileContents
    })
}

const formatVars = (modulePaths) => {
    const mappedPaths = {}
    _.each(modulePaths, (modulePath) => {
        const moduleName = modulePath.split('/').pop()
        mappedPaths[modulePath] = pascalCase(moduleName)
    })
    return mappedPaths
}

const modules = getModuleOutputs()

const createModuleDefinitions = (modules) => {
    const moduleTemplate = _.template(`define(<%=moduleNames%>, function (<%=moduleVars%>) { return { <% _.forEach(moduleExports, function(moduleVar, key) { %>"<%- key %>": <%- moduleVar %>, <% }); %> } })`)

    const files = _.map(modules, (definition) => {
        const moduleNames = JSON.stringify(definition.exports)
        const moduleVars = formatVars(definition.exports)
        return {
            fileName: definition.path,
            contents: moduleTemplate({
                moduleNames,
                moduleVars: _.values(moduleVars).join(', '),
                moduleExports: moduleVars
            })
        }
    })

    return files

}

const generateModules = (name, contents) => {
    const moduleDefinitions = createModuleDefinitions(modules)
    _.each(moduleDefinitions, (def) => {
        console.log(def.fileName)
        fs.writeFileSync(path.resolve(__dirname, def.fileName), def.contents, 'utf8')
    })
}


generateModules()

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
