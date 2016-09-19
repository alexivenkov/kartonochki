<?php
# Модель для работы с API Почты России

function getOperationHistory($track_id, $post_login, $post_password)
{
	$response = makeRequest($track_id, $post_login, $post_password);
	return parseResponse($response);
}

function makeRequest($track_id, $post_login, $post_password)
{
	$request = '<?xml version="1.0" encoding="UTF-8"?>
                <soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope" xmlns:oper="http://russianpost.org/operationhistory" xmlns:data="http://russianpost.org/operationhistory/data" xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/">
                <soap:Header/>
                <soap:Body>
                   <oper:getOperationHistory>
                      <data:OperationHistoryRequest>
                         <data:Barcode>'.$track_id.'</data:Barcode>  
                         <data:MessageType>0</data:MessageType>
                         <data:Language>RUS</data:Language>
                      </data:OperationHistoryRequest>
                      <data:AuthorizationHeader soapenv:mustUnderstand="1">
                         <data:login>'.$post_login.'</data:login>
                         <data:password>'.$post_password.'</data:password>
                      </data:AuthorizationHeader>
                   </oper:getOperationHistory>
                </soap:Body>
             </soap:Envelope>';

	$client = new SoapClient("https://tracking.russianpost.ru/rtm34?wsdl",  array('trace' => 1, 'soap_version' => SOAP_1_2));

	return $client->__doRequest($request, "https://tracking.russianpost.ru/rtm34", "getOperationHistory", SOAP_1_2);
}

function parseResponse($response)
{
	$xml = simplexml_load_string($response);
	$ns = $xml->getNamespaces(true);
	foreach($ns as $key => $dummy)
	{
		if (strpos($key, 'ns') === 0)
		{
			if (isset($nsKey1))
				$nsKey2 = $key;
			else
				$nsKey1 = $key;
		}
	}
	
	if (isset($nsKey2))
		$records = $xml->children($ns['S'])->Body->children($ns[$nsKey1])->children($ns[$nsKey2])->OperationHistoryData->historyRecord;
	else
		return false;

	if ($xml->children($ns['S'])->Body && $records)
	{
		$trackData = array();
		foreach($records as $rec)
		{
			$operation = array();
			
			$operation['status']	= (string) $rec->OperationParameters->OperType->Name;
			$operation['operation']	= (string) $rec->OperationParameters->OperAttr->Name;
			$operation['place']		= (string) $rec->AddressParameters->OperationAddress->Index . ' ' . $rec->AddressParameters->OperationAddress->Description;
			$operation['date']		= (string) $rec->OperationParameters->OperDate;
			$trackData[]			= $operation;
		}
		return $trackData;
	}
	else
		return false;
}