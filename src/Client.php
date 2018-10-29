<?php

namespace SHL\SdiClient;

use SHL\SdiClient\Exceptions\RequestFailureException;

class Client {
	
	private $Endpoint;
	
	private $Token;
	
	/**
	 * Il numero di secondi massimo per l'esecuzione della curl
	 * @var int
	 */
	private $Timeout = 3600;
	
	
	/**
	 * Il numero di secondi massimo di attesa per il tentativo di connession
	 * @var int
	 */
	private $TryConnectionTimeout = 120;
	

	public function __construct( $endpoint, $username, $apiToken ) {
		echo 'ciao';
		$this->Endpoint = $endpoint;
		$this->Token = $username . '.' . $apiToken;
	}
	
	
	public function setTimeout( int $timeout ) {
		$this->Timeout = $timeout;
	}

	public function setTryConnectionTimeout( int $tryConnectionTimeout ) {
		$this->TryConnectionTimeout = $tryConnectionTimeout;
	}

		
	private function curl( $verb, $request, $json = null ) {
		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, $verb );
		curl_setopt( $ch, CURLOPT_URL, $this->Endpoint . $request );
		
		//gestione oggetto per richiesta
		curl_setopt( $ch, CURLOPT_POST, ! is_null( $json ) );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $json );
		
		curl_setopt($ch, CURLOPT_HTTPHEADER, [
			'Authorization' => $this->Token,
			'Content-Type' => 'application/json'
		]);

		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, true );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 2 );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 );
		curl_setopt( $ch, CURLOPT_TIMEOUT, $this->Timeout );
		curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, $this->TryConnectionTimeout );
		
		$result = curl_exec( $ch );
		$curlErrorMessage = curl_error( $ch );
		$curlErrorNumber = curl_errno( $ch );
		$httpStatusCode = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
		
		curl_close($ch);
		
		if( $curlErrorMessage ) {
			throw new RequestFailureException( sprintf( 'Curl "%s%s" error: [%s] %s', $this->Endpoint, $request, $curlErrorNumber, $curlErrorMessage ) );
		}
		
		if ( $httpStatusCode != 200 ) {
			//gestione con oggetto di errore			
			throw new RequestFailureException( sprintf( 'Http request "%s%s" error' ) );
		}
		
		//gestione con oggetto della richiesta
		return $result;
	}
	
	
	public function getDocumentSent() {
		return $this->curl( 'GET', 'document_sent' );
	}
}
