const rootDir = process.cwd();
const path = require('path');
const bundlePaths = require(path.resolve(rootDir, './web/js/require-paths'));
const BUNDLE_LESS_PATH = 'public/less/index.less'
const fs = require('fs')
const lessc = require('less')

function collectBundleStyles(bundlePaths) {
    const styles = bundlePaths.map(bundle => {
        return  path.dirname(bundle)
                .replace('config', BUNDLE_LESS_PATH);
    })

    const imports = []

    console.log('Compiling less\n')
    for (style of styles) {
        const absolutePath = path.resolve(rootDir, style.replace('/srv/pim', '.'))

        try {
            const contents = fs.readFileSync(absolutePath, {
                encoding: 'utf-8'
            })

            console.log('-', absolutePath)
            imports.push(contents)
        } catch(e) {}
    }

    console.log('\n')
    return imports;
}

function getProcessor(less) {

    function Processor(options) {
        this.options = options || {replace: []};
        this._visitor = new less.visitors.Visitor(this);
    }

    Processor.prototype = {
        isReplacing: true,
        isPreEvalVisitor: true,
        run: function (root) {
            return this._visitor.visit(root);
        },
        visitUrl: function (URLNode, visitArgs) {
            var path = URLNode.value.value;

            if (!path) return;

            if (typeof URLNode.value._fileInfo !== "undefined") {
                var absPattern = new RegExp("^([a-zA-Z]+\:\/\/|\/|data\:).+");
                if (!absPattern.test(URLNode.value.value)) {
                    path = URLNode.value._fileInfo.currentDirectory + path;
                }
            }

            this.options.replace.forEach(function(repl) {
                var pattern = new RegExp(repl.search, "g");
                path = path.replace(pattern, repl.replace)
            });

            URLNode.value.value = path;
            return URLNode;
        }
    };
    return Processor;
};


const AbsoluteURLs = function() {
    return {
        options: {},
        install(less, pluginManager) {
            var Processor = getProcessor(less);
            pluginManager.addVisitor(new Processor(this.options));
        },
        setOptions: function(options) {
            this.options = options
        }
    }
}

const plugin = new AbsoluteURLs()
plugin.setOptions({
    replace: [{
        search: './web/bundles',
        replace: '/bundles' }
    ]
})

const appStyles = collectBundleStyles(bundlePaths).join('')
const compiledStyles = lessc.render(appStyles, {
    plugins: [plugin],
    sourceMap: {sourceMapFileInline: true}
})
    .then(function(output) {
        try {
            fs.writeFileSync(path.resolve(rootDir, './web/css/pim.css'), output.css, 'utf-8')
            console.log('Compiled')
        } catch(e) {
            console.log("Error writing file", e)
        }
        // output.css = string of css
        // output.map = string of sourcemap
        // output.imports = array of string filenames of the imports referenced
    },
    function(error) {
        console.log("Error rendering", error)
        process.exit(1)
    }).catch(error => console.log('Other error', error));


