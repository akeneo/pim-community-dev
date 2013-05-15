/**
 * PimAjax class purposes an easier way to call jQuery Ajax component
 * 
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * 
 * @uses PimDialog
 * 
 * Example :
 *      PimAjax.remove(myURL, '');
 *      if (PimAjax.isSuccessfull()) return true;
 */
var PimAjax = new function() {
    /**
     * Synchronous request call with jQuery ajax component
     * @param string type Request type
     * @param string url The url to which the request is sent
     * @param string|PlainObject data Data to be sent to the server
     */
    this.ajax = function(type, url, data) {
        $.ajax({
            async: PimAjax.isAsync(),
            data: data,
            type: type,
            url: url,
            success : function (data, textStatus, jqXHR) {
                PimAjax.success = true;
            },
            error: function (xhr, textStatus, errorThrown) {
                PimDialog.alert(xhr.responseText, xhr.statusText);
                PimAjax.success = false;
            }
        });
    },
    
    /**
     * Call jquery DELETE ajax request
     * @param string url The url to which the request is sent
     * @param string|PlainObject data Data to be sent to the server
     */
    this.delete = function(url, data) {
        PimAjax.ajax('DELETE', url, data);
    },
    
    /**
     * Call jquery GET ajax request
     * @param string url The url to which the request is sent
     * @param string|PlainObject data Data to be sent to the server
     */
    this.get = function(url, data) {
        PimAjax.ajax('GET', url, data);
    },
    
    /**
     * Call jquery POST ajax request
     * @param string url The url to which the request is sent
     * @param string|PlainObject data Data to be sent to the server
     */
    this.post = function(url, data) {
        PimAjax.ajax('POST', url, data);
    },
    
    /**
     * Predicate to know last ajax request success result
     * @return boolean
     */
    this.isSuccessfull = function() {
        return PimAjax.success;
    },
    
    /**
     * Set Ajax request asynchronous or not
     * @param boolean async
     */
    this.setAsync = function(async) {
        PimAjax.async = async;
    },
    
    /**
     * Predicate to know if ajax request is asynchronous or not
     * Returns false by default
     * @return boolean
     */
    this.isAsync = function() {
        return PimAjax.async || false;
    }
}
