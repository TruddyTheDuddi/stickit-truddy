/**
 * Base Ajax that allows to specify a callback function to 
 * be called when the request is completed.
 * 
 * Usually the functions take the following parameters:
 * @param {string} url - The url to send the request to
 * @param {object} callbacks - When the request is completed
 * @param {object} payload - The data to send along the request
 * 
 * The `callbacks` is an object with the following properties:
 * * `success`: The function to call if the request succeeds.
 * * `error`: The function to call if the request fails.
 * 
 * None of the 2 callbacks are mandatory. If success is provided
 * but not the error, the success will be called in case of error
 * with the expected format, where msg is the fetch's error message:
 * {success: false, msg: "..."}
 * 
 * If no callbacks are provided, the default success and error will
 * print or throw the result in the console.
 */
export let AJAX = {
    /**
     * Performs a GET request to the specified URL.
     * @param {*} url 
     * @param {*} callbacks object with the following properties:
     * * `success`: The function to call if the request succeeds.
     * * `error`: The function to call if the request fails.
     * @param {*} payload object (dictionnary) with the data to send
     */
    get: function(url, callbacks = null, payload = null) {
        const options = {
            method: "GET",
        };

        // Copy the callbacks to a local variable for easier access
        let _callbacks = {
            success: callbacks == null ? null : callbacks.success,
            error: callbacks == null ? null : callbacks.error,
        };

        // Perform the request
        fetch(url + this.encodeQueryData(payload), options)
            .then(async response => {
                // Is the response OK? 2xx status codes
                if (response.ok) {
                    return response.text().then(text => {
                        // Is it convertible to JSON?
                        try {
                            return JSON.parse(text);
                        } catch (err) {
                            throw new Error(`Server response error (unable to parse JSON)`);
                        }
                    });
                } else {
                    throw new Error(`Request to "${url}" failed: ${response.status} (${response.statusText})`);
                }
            })
            .then(data => {
                if (_callbacks.success) {
                    // A success callback was provided
                    _callbacks.success(data);
                } else {
                    // Default success handling
                    console.log(data);
                }
            })
            .catch(error => {
                if (_callbacks.error != null) {
                    // Use error callback
                    _callbacks.error(error.message);
                } else {
                    // Use success callback and let caller handle the error
                    if(_callbacks.success){
                        _callbacks.success({
                            success: false,
                            msg: error.message,
                        });
                    } else {
                        // Default error handling
                        console.warn(error.message);
                    }
                }
            });
    },

    // post: function(url, callbacks, payload) {
    //     return this.fetch(url, "POST", payload, callbacks.success, null, callbacks.error);
    // },

    /**
     * Object turned into a string for the GET URI 
     * @param {object} data - The object to convert
     */
    encodeQueryData: function(data) {
        const ret = [];
        for (let d in data)
            ret.push(encodeURIComponent(d) + '=' + encodeURIComponent(data[d]));
        return "?" + ret.join('&');
    }
}
