<?php
/**
 * @package: oracleSoapApi
 * @link: https://github.com/feltkamptv/oraclechatapi/
 * @developer: Feltkamp.tv Multimedia Productions
 * @email: info@feltkamp.tv
 * @tel: +31 (0) 20 785 4487
 * @website: http://www.feltkamp.tv
 * @author: Pim Feltkamp
 * @copyright 2016 Pim Feltkamp
 * @license: http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 * @note: This program is distributed in the hope that it will be useful - WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.
**/

class oracleSoapApi{

	protected $wsdl = false;
	protected $username = false;
	protected $password = false;
	protected $appId = false;
	protected $sessionId = false;
	protected $interfaceName = false;
	protected $interfaceId = false;
	
	public function __construct($wsdl, $username, $password, $appId, $sessionId, $interfaceId, $interfaceName){
	$this->wsdl = $wsdl;
	$this->username = $username;
	$this->password = $password;
		$this->appId = $appId;
		$this->sessionId = $sessionId;
		$this->interfaceId = $interfaceId;
		$this->interfaceName = $interfaceName;
		$this->error = false;
		$this->error_curl = false;
		$this->httpcode = false;
		$this->retry = false;
		$this->clientTransactionID = 0;
		$this->chatSessionToken = false;
		$this->sitename = false;
	}// end function
	
	/** 
	* SET WSDL
	*
	* This function will set the wsdl parameter.
	*
	* @param string $wsdl
	*
	* @return: true
	**/
	public function setWSDL($wsdl){
		$this->wsdl = $wsdl;
		return true;
	}// end function
	
	/** 
	* SET SESSION ID
	*
	* This function will set the sessionId parameter.
	*
	* @param string $id
	*
	* @return: true
	**/
	public function setSessionID($id){
		$this->sessionId = $id;
		return true;
	}// end function
	
	/** 
	* SET CHAT SESSION TOKEN
	*
	* This function will set the chatSessionToken parameter.
	*
	* @param string $token
	*
	* @return: true
	**/
	public function setChatSessionToken($token){
		$this->chatSessionToken = $token;
		return true;
	}// end function
	
	/** 
	* SET SITENAME
	*
	* This function will set the sitename parameter.
	*
	* @param string $sitename
	*
	* @return: true
	**/
	public function setSitename($sitename){
		$this->sitename = $sitename;
		return true;
	}// end function
	
	/** 
	* CHECK AVAILIBILITY
	*
	* This function will check the availability of the Chat Service. 
	* The Chat service will be available if it is in operating hours and not in holiday
	*
	* @return: boolean
	**/
	public function checkAvailability(){
		$opening_hours = $this->call('GetChatOperatingHours', array());
		$opening_hours = $this->parseResult('GetChatOperatingHours', $opening_hours);
		if($opening_hours['in_holiday'][0] == 'false' && $opening_hours['in_operating_hours'][0] == 'true'){
			return true;
		}else{
			return false;
		}
	}// end function
	
	/** 
	* GET CHAT URL
	*
	* This function will perform the GetChatUrl Soap API call. 
	* It will return the chat url, chat token and sitename if the call was successfull. 
	*
	* @return: array on success, false on failure.
	**/
	public function getChatUrl(){
		$chat_soap = $this->call('GetChatUrl', array('UrlType' => 'ENDUSER'));
		if(!$chat_soap){
			return false;
		}
		$chat_soap = $this->parseResult('GetChatUrl', $chat_soap);
		if(!$chat_soap){
			return false;
		}else{
			return $chat_soap;
		}
	}// end function
	
	/** 
	* REQUEST CHAT
	*
	* This function will call the chat url, set the wsdl, sitename and token and perform the RequestChat SOAP API call.
	*
	* @param string $phonenumber, array $data
	*
	* @return: string $session_id on success, false on failure
	**/
	public function requestChat($phonenumber, $data){
		$chat_soap = $this->getChatUrl();
		if(!$chat_soap){
			return false;
		}
		$this->setWSDL($chat_soap['chat_url']);
		$this->setSitename($chat_soap['sitename']);
		$this->setChatSessionToken($chat_soap['chat_token']);
		$request_chat = $this->call('RequestChat', $data);
		if($this->error){
			return false;
		}else{
			$request_chat = $this->parseResult('RequestChat', $request_chat);
			return $request_chat['session_id'];
		}
	}// end function
	
	/** 
	* REQUEST CHAT ONLY
	*
	* This function will only perform the RequestChat SOAP API call.
	*
	* @param string $phonenumber, array $data
	*
	* @return: string $session_id on success, false on failure
	**/
	public function requestChatOnly($phonenumber, $data){
		$request_chat = $this->call('RequestChat', $data);
		if($this->error){
			return false;
		}else{
			$request_chat = $this->parseResult('RequestChat', $request_chat);
			return $request_chat['session_id'];
		}
	}// end function
	
	/** 
	* RESUME CHAT
	*
	* This function will call the chat url, set the wsdl, sitename and token.
	*
	* @param string $phonenumber, array $data
	*
	* @return: boolean
	**/
	public function resumeChat($phonenumber, $data){
		$chat_soap = $this->getChatUrl();
		if(!$chat_soap){
			return false;
		}
		$this->setWSDL($chat_soap['chat_url']);
		$this->setSitename($chat_soap['sitename']);
		$this->setChatSessionToken($chat_soap['chat_token']);
		return true;
	}// end function
	
	/** 
	* SEND MSG
	*
	* This function will perform the PostChatMessage SOAP API call.
	*
	* @param string $msg
	*
	* @return: boolean
	**/
	public function sendMsg($msg){
		$post_message = $this->call('PostChatMessage', array('Body'=>$msg));
		if(!$post_message){
			return false;
		}else{
			return true;
		}
	}// end function
	
	/** 
	* CHECK ACTIVE CHAT
	*
	* This function will check if a chat is still active and available.
	*
	* @return: boolean
	**/
	public function checkActiveChat(){
		$post = array('Mode'=>'LISTENING');
		$post_message = $this->call('SendActivityChange', $post);
		if(!$post_message){
			return false;
		}else{
			return true;
		}
	}// end function
	
	/** 
	* SET TYPING MESSAGE
	*
	* This function will perform the SendActivityChange SOAP API call.
	*
	* @param boolean $typing
	*
	* @return: boolean
	**/
	public function setTypingMessage($typing){
		if($typing){
			$post = array('Mode'=>'RESPONDING');
		}else{
			$post = array('Mode'=>'LISTENING');
		}
		$post_message = $this->call('SendActivityChange', $post);
		if(!$post_message){
			return false;
		}else{
			return true;
		}
	}// end function
	
	/** 
	* GET REQUEST HEADER
	*
	* This function will create the request header for a SOAP API call.
	*
	* @param string $action
	*
	* @return: string $header
	**/
	public function getRequestHeader($action){
		$header = '<soapenv:Header>';
		if($action == 'GetChatUrl' || $action == 'GetChatOperatingHours'){
			$header .= '<v1:ClientRequestHeader><v1:AppID>'.$this->appId.'</v1:AppID></v1:ClientRequestHeader>';
		}else{
			$header .= '<v1:ChatClientInfoHeader><v1:AppID>'.$this->appId.'</v1:AppID>';
			if($this->sessionId){
				$header .= '<v1:SessionID>'.$this->sessionId.'</v1:SessionID>';
			}
		    $header .= '</v1:ChatClientInfoHeader>';
		}
		
		$header .= '<wsse:Security xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd" mustUnderstand="1"><wsse:UsernameToken><wsse:Username>'.$this->username.'</wsse:Username><wsse:Password Type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText">'.$this->password.'</wsse:Password></wsse:UsernameToken></wsse:Security></soapenv:Header>';
		return $header;
	}// end function
	
	/** 
	* GET TRANSACTION REQUEST DATA
	*
	* This function will create the TransactionRequestData for a SOAP API call.
	*
	* @return: string $result
	**/
	public function getTransactionRequestData(){
		$result = '<v11:TransactionRequestData>
					<v11:ClientRequestTime>'.date("c").'</v11:ClientRequestTime>
					<v11:ClientTransactionID>'.$this->getClientTransactionID().'</v11:ClientTransactionID>
					<v11:SiteName>'.$this->sitename.'</v11:SiteName>
				 </v11:TransactionRequestData>';
		return $result;
	}// end function
	
	/** 
	* GET CLIENT TRANSACTION ID
	*
	* This function will count up the clientTransactionID and return the new number.
	*
	* @return: int $this->clientTransactionID
	**/
	public function getClientTransactionID(){
		$this->clientTransactionID++;
		return $this->clientTransactionID;
	}// end function
	
	/** 
	* GET SOAP REQUEST
	*
	* This function will create the SOAP request data for a SOAP API call.
	*
	* @param string $action, array $params
	* 
	* @return: string $raw_xml
	**/
	public function getSoapRequest($action, $params){
		if($action == 'GetChatUrl' || $action == 'GetChatOperatingHours'){
			$raw_xml = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:v1="urn:messages.chat.ws.rightnow.com/v1_1">'.$this->getRequestHeader($action).'<soapenv:Body>';
		}else{
			$raw_xml = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:v1="urn:messages.common.chat.ws.rightnow.com/v1" xmlns:v11="urn:messages.enduser.chat.ws.rightnow.com/v1">'.$this->getRequestHeader($action).'<soapenv:Body>';
		}
		switch($action){
			case 'RequestChat':
				$raw_xml .= '<v11:RequestChat>'.$this->getTransactionRequestData();
				$raw_xml .= '<v11:CustomerInformation>';
				if(array_key_exists('EMailAddress', $params['CustomerInformation'])){
            		$raw_xml .= '<v1:EMailAddress>'.$params['CustomerInformation']['EMailAddress'].'</v1:EMailAddress>';
				}
				if(array_key_exists('FirstName', $params['CustomerInformation'])){
            		$raw_xml .= '<v1:FirstName>'.$params['CustomerInformation']['FirstName'].'</v1:FirstName>';
				}
				if(array_key_exists('LastName', $params['CustomerInformation'])){
            		$raw_xml .= '<v1:LastName>'.$params['CustomerInformation']['LastName'].'</v1:LastName>';
				}
				if(array_key_exists('ContactID', $params['CustomerInformation'])){
            		$raw_xml .= '<v1:ContactID id="'.$params['CustomerInformation']['ContactID'].'"/>';
				}
				$raw_xml .= '<v1:InterfaceID><v1:ID id="'.$this->interfaceId.'"/><v1:Name>'.$this->interfaceName.'</v1:Name></v1:InterfaceID>';
         		$raw_xml .= '</v11:CustomerInformation>';
         		$raw_xml .= '<v11:ChatSessionToken>'.$this->chatSessionToken.'</v11:ChatSessionToken>';
				$raw_xml .= '</v11:RequestChat>';
				break;
			
			case 'GetChatUrl':
				$raw_xml .= '<v1:GetChatUrl><v1:UrlType>'.$params['UrlType'].'</v1:UrlType></v1:GetChatUrl>';
				break;
			
			case 'PostChatMessage':
				$raw_xml .= '<v11:PostChatMessage>'.$this->getTransactionRequestData();
         		$raw_xml .= '<v11:Body>'.$params['Body'].'</v11:Body></v11:PostChatMessage>';
				break;
			
			case 'TerminateChat':
				$raw_xml .= '<v11:TerminateChat>'.$this->getTransactionRequestData();
         		$raw_xml .= '</v11:TerminateChat>';
				break;
			
			case 'RetrieveMessages':
				$raw_xml .= '<v11:RetrieveMessages>'.$this->getTransactionRequestData();
         		$raw_xml .= '</v11:RetrieveMessages>';
				break;
			
			case 'SendActivityChange':
				$raw_xml .= '<v11:SendActivityChange>'.$this->getTransactionRequestData();
         		$raw_xml .= '<v11:Mode>'.$params['Mode'].'</v11:Mode></v11:SendActivityChange>';
				break;
				
			case 'GetChatOperatingHours':
				$raw_xml .= '<v1:GetChatOperatingHours/>';
				break;
				
			default:
				   	if(count($params) > 0){
						$raw_xml .= '<v1:'.$action.'>';
					  	foreach($params as $param_key => $param_val){
							if(is_array($param_val)){
								$raw_xml .= '<v1:'.$param_key.'>';
								foreach($param_val as $param_val_key => $param_val_val){
									$raw_xml .= '<v1:'.$param_val_key.'>'.$param_val_val.'</v1:'.$param_val_key.'>';
								}// foreach
								$raw_xml .= '</v1:'.$param_key.'>';
							}else{
						 		$raw_xml .= '<v1:'.$param_key.'>'.$param_val.'</v1:'.$param_key.'>';
							}
						}// foreach
						$raw_xml .= '</v1:'.$action.'>';
					}else{
						$raw_xml .= '<v1:'.$action.'/>';
					}
					break;
		}//switch
		$raw_xml .= '</soapenv:Body></soapenv:Envelope>';
		return $raw_xml;
	}// end function
	
	/** 
	* PARSE RESULT
	*
	* This function will parse the result from a SOAP API call.
	*
	* @param string $action, string $response
	* 
	* @return: array $result
	**/
	public function parseResult($action, $response){
		$result = array();
		if(!$response){
			return $result;
		}
		
		$xml = simplexml_load_string($response, NULL, NULL, "http://schemas.xmlsoap.org/soap/envelope/");
		
		$ns = $xml->getNamespaces(true);
		
		switch($action){
			case 'RequestChat':
				$soap = $xml->children($ns['S']);
				$res = $soap->Body->children($ns['ns3']);
				$result['session_id'] = (string) $res->RequestChatResponse->SessionID;
				break;
			
			case 'GetChatUrl':
				$soap = $xml->children($ns['soapenv']);
				$res = $soap->Body->children($ns['n0']);
				
				$result['chat_url'] = (string) $res->GetChatUrlResponse->GetChatUrlResult->ChatUrl;
				$result['chat_token'] = (string) $res->GetChatUrlResponse->GetChatUrlResult->ChatToken;
				$result['sitename'] = (string) $res->GetChatUrlResponse->GetChatUrlResult->SiteName;
				break;
			
			case 'GetChatOperatingHours':
				$soap = $xml->children($ns['soapenv']);
				$res = $soap->Body->children($ns['n0']);
				$result['opening_hours'] = (array) $res->GetChatOperatingHoursResponse->GetChatOperatingHoursResult->Intervals->Interval;
				$result['in_operating_hours'] = (array) $res->GetChatOperatingHoursResponse->GetChatOperatingHoursResult->InOperatingHours;
				$result['in_holiday'] = (array) $res->GetChatOperatingHoursResponse->GetChatOperatingHoursResult->InHoliday;
				break;
			case 'PostChatMessage':
				break;
			
			case 'TerminateChat':
				break;
			
			case 'RetrieveMessages':
				$soap = $xml->children($ns['S']);
				$res = $soap->Body->children($ns['ns3']);
				$result['messages'] = (array) $res->RetrieveMessagesResponse->SystemMessages->RNChatMessagePostedMessage; 
				break;
			
			case 'SendActivityChange':
				break;
			
			default:
				$soap = $xml->children($ns['S']);
				$result = (array) $soap->Body->children($ns['ns3']);
				break;
		}//switch
		return $result;
	}// end function
	
	/** 
	* CALL
	*
	* This function will perform the SOAP API call with Curl.
	*
	* @param string $action, array $params, string $request (optional)
	* 
	* @return: string $response
	**/
	public function call($action, $params, $request = false){
		$this->error = false;
		$this->error_curl = false;
		if(!$request){
			$this->request = $this->getSoapRequest($action, $params);
		}else{
			$this->request = $request;
		}
		$headers = array(
			'Content-Type: text/xml;charset=UTF-8',
			'Connection: Keep-Alive', 
			'SOAPAction: "'.$action.'"',
			'Accept-Encoding: gzip,deflate',
			'User-Agent: Apache-HttpClient/4.1.1 (java 1.5)',
			'Cache-Control: no-cache',
			'Pragma: no-cache',
			'Content-length: '.strlen($this->request),
		);
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_URL, $this->wsdl);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 20);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $this->request);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		
		$response = curl_exec($ch);
		if(!$response){
			$this->error_curl = curl_error($ch);
		}
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		if($http_code == 200){
			$this->retry = false;
			return $this->decodeGzip($response);
		}elseif(empty($http_code) && !$this->retry){
			$this->retry = true;
			sleep(500000);
			return $this->call($action, $params, $request);
		}else{
			$this->retry = false;
			$this->httpcode = $http_code;
			$this->error = $this->decodeGzip($response);
			return false;
		}
	}// end function
	
	/** 
	* DECODE GZIP
	*
	* This function will decode a gzipped string, but only if it is gzipped.
	*
	* @param string $response
	* 
	* @return: string $response
	**/
	public function decodeGzip($response){
		$is_gzip = 0 === mb_strpos($response, "\x1f" . "\x8b" . "\x08", 0, "US-ASCII");
		if($is_gzip){
			return gzdecode($response);
		}else{
			return $response;
		}
	}// end function
	
	/** 
	* GET ERROR
	*
	* This function will return the error output.
	*
	* @return: string
	**/
	public function getError(){
		return $this->error;
	}// end function
}// end class
