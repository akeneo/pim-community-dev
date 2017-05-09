const path = require('path')
const yaml = require('yamljs')
const fs = require('fs')
const _ = require('lodash')
const pascalCase = require('pascal-case')
const configModuleTemplate = fs.readFileSync('./config-module-template.html', 'utf8')

const bundleDirectory = './src/Pim/Bundle'
const requirePath = _.template(`${bundleDirectory}/<%=bundleName%>/Resources/config/requirejs.yml`)

const moduleOutputs = {
    fetchers: {
        inputPath: `config.config['pim/fetcher-registry'].fetchers`,
        outputPath: `${bundleDirectory}/EnrichBundle/Resources/public/js/config/fetchers.js`,
    },
    controllers: {
        inputPath: `config.config['pim/controller-registry'].controllers`,
        outputPath: `${bundleDirectory}/EnrichBundle/Resources/public/js/config/controllers.js`,
    }
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

const getModuleOutputs = (configFiles) => {
    return _.map(moduleOutputs, (output) => {
        const contents = {}

        _.each(configFiles, (file) => {
            const props = _.get(file, output.inputPath) || {}
            _.each(props, (prop, name) => {
                prop.resolvedModule = pascalCase(prop.module)
                if (typeof prop === 'string') {
                    contents[name] = { module: prop }
                } else {
                    contents[name] = prop
                }
            })
        })

        return {
            fileName: output.outputPath,
            modules: contents
         }
    })
}

const configFiles = getConfigFiles()
const files = getModuleOutputs(configFiles)

files.forEach((file) => {
    const fileTemplate = _.template(configModuleTemplate)
    const paths = _.compact(_.uniq(_.map(file.modules, 'module')))
    const values =  _.map(paths, path => pascalCase(path))

    const fileContents =  fileTemplate({
        paths: JSON.stringify(paths),
        values,
        modules: file.modules
    })

    fs.writeFileSync(path.resolve(__dirname, file.fileName), fileContents, 'utf8')
})


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
