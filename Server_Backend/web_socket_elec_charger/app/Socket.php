<?php

namespace MyApp;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class Socket implements MessageComponentInterface {

    public function __construct()
    {
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn) {

        // Store the new connection in $this->clients
      	

        //Only for PC client //For other clients form dynamic token creation module
        $jwt_token="eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiaWF0IjoxNTE2MjM5MDIyfQ.WSAs_qh3A0UU-WwcxZwVYgOFzVhs1jqkZEBYMYsInm4";


	$querystring = $conn->httpRequest->getUri()->getQuery();
        parse_str($querystring,$queryarray);

	echo $queryarray['id']."\r\n";
	echo $queryarray['token']."\r\n";
	
        //$id=substr($querystring,strpos($querystring,'=')+1);

	if( $jwt_token!=$queryarray['token'])
	{
		echo "Unauthorized access"."\r\n";
		return;
	}

        $this->clients->attach($conn);

        echo "New connection! ({$conn->resourceId})\n";

    }

    public function onMessage(ConnectionInterface $from, $msg) {

        foreach ( $this->clients as $client ) {

            if ( $from->resourceId == $client->resourceId ) {

		echo $from->resourceId; 
		echo "\r\n";
		echo $msg;
		echo "\r\n";

		$obj=json_decode($msg,true);
		//echo $obj;
		//echo $obj[0];
		//echo " *************\r\n";
		//echo $obj[1];
		//echo "***************\r\n";
		//echo $obj[2].'\r\n';
		//echo " **************\r\n";


		if($obj[2]=='BootNotification')
		{
			echo $obj[3]['chargePointVendor'].'\r\n';
			echo $obj[3]['chargePointModel'].'\r\n';
			//Increment request number
		}	

		//Enter In Database


		$rsp=$obj;
		$rsp[0]=$rsp[0]+1;

		echo "New response message id is   ".$rsp[0]."\r\n";		


		$rsp=json_encode($rsp);

		$client->send( "Client $from->resourceId said $rsp" );
		echo "\r\nresponse sent back\r\n";
                break;
            }
	
        }
    }

    public function onClose(ConnectionInterface $conn) {
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
    }
}
