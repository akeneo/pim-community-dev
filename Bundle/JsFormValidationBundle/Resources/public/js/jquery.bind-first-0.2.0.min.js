/*
 * jQuery.bind-first library v0.2.0
 * Copyright (c) 2013 Vladimir Zhuravlev
 *
 * Released under MIT License
 *
 * Date: Sun Jan 20 16:12:09 ICT 2013
 **/(function(a){function e(b,c,e){var f=c.split(/\s+/);b.each(function(){for(var b=0;b<f.length;++b){var c=a.trim(f[b]).match(/[^\.]+/i)[0];d(a(this),c,e)}})}function d(a,d,e){var f=c(a),g=f[d];if(!b){var h=e?g.splice(g.delegateCount-1,1)[0]:g.pop();g.splice(e?0:g.delegateCount||0,0,h)}else e?f.live.unshift(f.live.pop()):g.unshift(g.pop())}function c(c){return b?c.data("events"):a._data(c[0]).events}var b=parseFloat(a.fn.jquery)<1.7;a.fn.bindFirst=function(){var b=a.makeArray(arguments),c=b.shift();c&&(a.fn.bind.apply(this,arguments),e(this,c));return this},a.fn.delegateFirst=function(){var b=a.makeArray(arguments),c=b[1];c&&(b.splice(0,2),a.fn.delegate.apply(this,arguments),e(this,c,!0));return this},a.fn.liveFirst=function(){var b=a.makeArray(arguments);b.unshift(this.selector),a.fn.delegateFirst.apply(a(document),b);return this}})(jQuery)