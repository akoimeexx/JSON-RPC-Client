<?php 
 /**     1         2         3         4         5         6         7         8
  * 5678901234567890123456789012345678901234567890123456789012345678901234567890
  * JSON-RPC client class module, handy implementation guide here:
  *                                         http://www.jsonrpc.org/specification
  */

    class JSON_RPC_Client {
        /**
         * Version of the JSON-RPC Specification we use. REQUIRED as input in 
         * all JSON-RPC requests for version 2.0
         */
        const JRPC_V = '2.0';
        
        // Object instance curl handle
        private $curl_handle;
        /**
         * Last JSON-RPC request id sent; useful for checking the returned 
         * response object id from __call() [Hint: They should match to tell 
         * which response was for which request].
         */
        private $last_id = null;
        
        
        /**
         * Constructor for the class; creates our connection url string based on
         * passed in arguments. Authentication/port info can just as easily be
         * passed in through $url directly (ie, standard httpbasic auth [
         * https://username:password@localhost:12345/]).
         */
        public function __construct($url, $port=null, $user=null, $pass=null) {
            /**
             * Add username/password if provided for basic http auth. If one is 
             * set both should be set; otherwise, do not add to url string.
             */
            if(($user != null) && ($pass != null)) {
                $url = substr_replace(
                    $url, 
                    sprintf("%s:%s@", $user, $pass), 
                    strpos($url, '://') + 3, 
                    0
                );
            }
            // Validate passed in port value is a number and add to url string.
            if(is_numeric($port)) {
                $url = substr_replace(
                    $url, 
                    sprintf(":%d", $port), 
                    strpos($url, '/', strpos($url, '://') + 3), 
                    0
                );
            }
            // Establish curl handle and assign it to this class instance (duh).
            $this->curl_handle = curl_init($url);
        }

        /**
         * Cleanup to run before destroying the class instance. Currently just 
         * closes the curl handle.
         */
        public function __destruct() {
            curl_close($this->curl_handle);
        }

        /**
         * Magic method to allow us to just make our json-rpc method requests
         * in an OO manner (ie, $JsonRpcInstance->method([method params, ...]);)
         */
        public function __call($rpc_method, $rpc_params) {
            /**
             * Assign a unique request id for use in comparing request/response 
             * messages.
             */
            $this->last_id = sprintf(
                "0x%04s:%s", 
                dechex(mt_rand(0, 65535)), 
                md5(time())
            );
            // Build an associative array for our json-rpc request...
            $arr_request = array(
                "jsonrpc"=>JSON_RPC_Client::JRPC_V, 
                "method"=>$rpc_method, 
                "id"=>$this->last_id, 
            );
            // ...and add params if they've been passed in...
            if(count($rpc_params) > 0) { $arr_request['params'] = $rpc_params; }
            // ...then encode as JSON to get ready to send.
            $json_request = json_encode($arr_request);

            /**
             * Configure curl for a custom post request, flag it to return the 
             * response instead of outputting directly, and configure our 
             * headers to describe the data being sent as json with appropriate 
             * content-length specified. Attach said json data.
             * 
             * Note: CURLOPT_VERBOSE=>TRUE can be uncommented if debugging is 
             * needed.
             */
            curl_setopt_array($this->curl_handle, array(
                /* CURLOPT_VERBOSE=>TRUE, */
                CURLOPT_CUSTOMREQUEST=>"POST", 
                CURLOPT_RETURNTRANSFER=>true, 
                CURLOPT_HTTPHEADER=>array(
                    'Content-Type: application/json', 
                    'Content-Length: ' . strlen($json_request)), 
                CURLOPT_POSTFIELDS=>$json_request)
            );
            // Execute and return am associative array.
            return json_decode(curl_exec($this->curl_handle), true);
        }
        
        // Getter function to retrieve last request id to compare with response.
        public function __last_id() {
            return $this->last_id;
        }
    }
?>
