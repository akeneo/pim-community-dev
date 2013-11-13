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
(function($){
		  
	$.fn.extend({
		outerHTML: function( value ){
			
			// If there is no element in the jQuery object
			if(!this.length)
				return null;
			// Returns the value
			else if(value === undefined){
                
                var element = (this.length) ? this[0] : this,
					result;

                // Return browser outerHTML (Most newer browsers support it)
                if(element.outerHTML)
                    result = element.outerHTML;
                // Return it using the jQuery solution
                else
					result = $(document.createElement("div")).append($(element).clone()).html();
				
				// Trim the result
				if(typeof result === "string")
					result = $.trim(result);
				
                return result;
                
            }
			// Deal with function
			else if( $.isFunction(value) ){
				
				this.each(function(i){
					var $this = $( this );
					$this.outerHTML( value.call(this, i, $this.outerHTML()) );
				});
				
			}
			// Replaces the content
            else {
				
				var $this = $(this),
					replacingElements = [],
					$value = $(value),
					$cloneValue;
				
				for(var x = 0; x < $this.length; x++){
					
					// Clone the value for each element being replaced
					$cloneValue = $value.clone(true);
					
					// Use jQuery to replace the content
					$this.eq(x).replaceWith($cloneValue);
					
					// Add the replacing content to the collection
					for(var i = 0; i < $cloneValue.length; i++)
						replacingElements.push($cloneValue[i]);
				
				}
				
				// Return the replacing content if any
				return (replacingElements.length) ? $(replacingElements) : null;

            }
	
		}
	});

})(jQuery);