/*!
 * OuterHTML v2.1.0
 *
 * http://www.darlesson.com/
 *
 * Copyright 2012, Darlesson Oliveira
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 *
 * @requires jQuery v1.4.0 or above
 *
 * Reporting bugs, comments or suggestions: http://darlesson.com/contact/
 * Documentation and other jQuery plug-ins: http://darlesson.com/jquery/
 * Donations are welcome: http://darlesson.com/donate/
 */
 
// Examples and documentation at: http://darlesson.com/jquery/outerhtml/

// jQuery outerHTML
(function(a){a.fn.extend({outerHTML:function(b){if(!this.length)return null;else if(b===undefined){var c=this.length?this[0]:this,d;if(c.outerHTML)d=c.outerHTML;else d=a(document.createElement("div")).append(a(c).clone()).html();if(typeof d==="string")d=a.trim(d);return d}else if(a.isFunction(b)){this.each(function(c){var d=a(this);d.outerHTML(b.call(this,c,d.outerHTML()))})}else{var e=a(this),f=[],g=a(b),h;for(var i=0;i<e.length;i++){h=g.clone(true);e.eq(i).replaceWith(h);for(var j=0;j<h.length;j++)f.push(h[j])}return f.length?a(f):null}}})})(jQuery)