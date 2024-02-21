function getProcessor(less) {
    function Processor(options) {
        this.options = options || { replace: [] };
        this._visitor = new less.visitors.Visitor(this);
    }

    Processor.prototype = {
        run: function (root) {
            return this._visitor.visit(root);
        },
        visitUrl: function (URLNode, visitArgs) {
            let path = URLNode.value.value;

            if (!path) return URLNode;

            if (typeof URLNode.value._fileInfo !== "undefined") {
                const containsURL = new RegExp("^([a-zA-Z]+\:\/\/|\/|data\:).+");
                if (!containsURL.test(URLNode.value.value)) {
                    path = URLNode.value._fileInfo.currentDirectory + path;
                }
            }

            this.options.replace.forEach(function(repl) {
                let pattern = new RegExp(repl.search, "g");
                path = path.replace(pattern, repl.replace)
            });

            URLNode.value.value = path;

            return URLNode;
        }
    };
    return Processor;
};

function RewriteImageURLs(options) {
    return {
        install(less, pluginManager) {
            const Processor = getProcessor(less);
            pluginManager.addVisitor(new Processor(options));
        }
    }
}

module.exports = RewriteImageURLs;
