JSON RPC Client
===============

Simple php class module to make JSON-RPC requests to a server, using curl libraries. Written to address the out-of-date resources widely available on the web, tested against a cryptocoin json-rpc server. Returns responses as an associative array.

Usage
=====

    <?php 
      require_once('./json-rpc_client.class.php');
    
      $rpc1 = new JSON_RPC_Client("https://localhost/", 12345, 'username', 'password');
      // or
      $rpc2 = new JSON_RPC_Client("https://username:password@localhost/", 54321);
      // or
      $rpc3 = new JSON_RPC_Client("https://username:password@localhost:35142/");
      // or
      $rpc4 = new JSON_RPC_Client("https://localhost:35142/", null, 'username', 'password');
    
      $res = $rpc1->rpc_methodname([rpc param args, ...]);
      printf("\n<pre>\n%s\n</pre>\n", print_r($res, true));
    ?>
