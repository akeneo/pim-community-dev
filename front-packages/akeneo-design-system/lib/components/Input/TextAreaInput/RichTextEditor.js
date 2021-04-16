"use strict";
var __assign = (this && this.__assign) || function () {
    __assign = Object.assign || function(t) {
        for (var s, i = 1, n = arguments.length; i < n; i++) {
            s = arguments[i];
            for (var p in s) if (Object.prototype.hasOwnProperty.call(s, p))
                t[p] = s[p];
        }
        return t;
    };
    return __assign.apply(this, arguments);
};
var __createBinding = (this && this.__createBinding) || (Object.create ? (function(o, m, k, k2) {
    if (k2 === undefined) k2 = k;
    Object.defineProperty(o, k2, { enumerable: true, get: function() { return m[k]; } });
}) : (function(o, m, k, k2) {
    if (k2 === undefined) k2 = k;
    o[k2] = m[k];
}));
var __setModuleDefault = (this && this.__setModuleDefault) || (Object.create ? (function(o, v) {
    Object.defineProperty(o, "default", { enumerable: true, value: v });
}) : function(o, v) {
    o["default"] = v;
});
var __importStar = (this && this.__importStar) || function (mod) {
    if (mod && mod.__esModule) return mod;
    var result = {};
    if (mod != null) for (var k in mod) if (k !== "default" && Object.prototype.hasOwnProperty.call(mod, k)) __createBinding(result, mod, k);
    __setModuleDefault(result, mod);
    return result;
};
var __rest = (this && this.__rest) || function (s, e) {
    var t = {};
    for (var p in s) if (Object.prototype.hasOwnProperty.call(s, p) && e.indexOf(p) < 0)
        t[p] = s[p];
    if (s != null && typeof Object.getOwnPropertySymbols === "function")
        for (var i = 0, p = Object.getOwnPropertySymbols(s); i < p.length; i++) {
            if (e.indexOf(p[i]) < 0 && Object.prototype.propertyIsEnumerable.call(s, p[i]))
                t[p[i]] = s[p[i]];
        }
    return t;
};
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.RichTextEditor = void 0;
var react_1 = __importStar(require("react"));
var react_draft_wysiwyg_1 = require("react-draft-wysiwyg");
var draftjs_to_html_1 = __importDefault(require("draftjs-to-html"));
var html_to_draftjs_1 = __importDefault(require("html-to-draftjs"));
var draft_js_1 = require("draft-js");
require("react-draft-wysiwyg/dist/react-draft-wysiwyg.css");
var editorStateToRaw = function (editorState) {
    return draftjs_to_html_1.default(draft_js_1.convertToRaw(editorState.getCurrentContent()));
};
var rawToEditorState = function (value) {
    var rawDraft = html_to_draftjs_1.default(value);
    if (!rawDraft || !rawDraft.contentBlocks) {
        return draft_js_1.EditorState.createEmpty();
    }
    return draft_js_1.EditorState.createWithContent(draft_js_1.ContentState.createFromBlockArray(rawDraft.contentBlocks));
};
var RichTextEditor = function (_a) {
    var value = _a.value, _b = _a.readOnly, readOnly = _b === void 0 ? false : _b, onChange = _a.onChange, rest = __rest(_a, ["value", "readOnly", "onChange"]);
    var _c = react_1.useState(rawToEditorState(value)), editorState = _c[0], setEditorState = _c[1];
    var handleChange = function (editorState) {
        setEditorState(editorState);
        onChange(editorStateToRaw(editorState));
    };
    return (react_1.default.createElement(react_draft_wysiwyg_1.Editor, __assign({ toolbarHidden: readOnly, readOnly: readOnly, toolbar: {
            options: ['inline', 'blockType', 'fontSize', 'fontFamily', 'list', 'link', 'embedded', 'image', 'remove'],
            inline: {
                options: ['bold', 'italic'],
            },
        }, onEditorStateChange: handleChange }, rest, { editorState: editorState })));
};
exports.RichTextEditor = RichTextEditor;
//# sourceMappingURL=RichTextEditor.js.map