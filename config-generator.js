const path = require('path')
const yaml = require('yamljs')
const fs = require('fs')
const _ = require('lodash')
const pascalCase = require('pascal-case')
const glob = require('glob')
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
        outputPath: `${bundleDirectory}/EnrichBundle/Resources/public/js/config/controllers.js`
    },
    form_extensions: {
        inputPath: 'extensions',
        outputPath: `${bundleDirectory}/EnrichBundle/Resources/public/js/config/form-dependencies.js`
    }
}

const getFormExtensionConfig = () => {
    const formExtensions = {}
    const files = glob.sync('./src/Pim/Bundle/EnrichBundle/Resources/config/form_extensions/**/*.yml')
    _.each(files, (fileName) => {
        _.each(getParsedFile(fileName).extensions, (options, name) => {
            formExtensions[name] = options
        })
    })
    return formExtensions
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

files.push({
    fileName: moduleOutputs.form_extensions.outputPath,
    modules: getFormExtensionConfig()
})

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
