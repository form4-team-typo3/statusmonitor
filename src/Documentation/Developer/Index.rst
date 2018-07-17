.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


.. _developer:

JSON
====

this a short overview of the structure of the JSON file that will be send.

.. code-block:: javascript

	{
		//if you have set a usernme/id in the scheduler task
		"id": "username",
		//if you have set a password in the scheduler task
		"password": "password",
		//TYPO3 Version
		"version":"8.7",
		//here follows a list of the typo3 extensions that are local extensions and installed.
		"modules": [
		{
			"name": "realurl",
			"version": "8.15"
		},
		{
			"name": "staticfilecache",
			"version": "2.1"
		},
			//and so on
		]
	}


Extending the JSON data
=======================

If you want to extend the send Data , you can declare a signal like this in your ext_localconf.php

.. code-block:: php

	$signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(Dispatcher::class);
	$signalSlotDispatcher->connect(
		\FORM4\Statusmonitor\Utility\StatusmonitorUtility::class,
		'AddToDataToArrayBeforeJsonEncode',
		\YourVendor\YourExtension\YourPath\YourClass::class,
		'NameOfYOurMethod' 
	);

In your target method you will get one parameter, which is the parent object /\FORM4/\Statusmonitor/\Utility/\StatusmonitorUtility.
There are 3 Methods in the parent object available which might be helpful to manipulate the array before it is decoded as a JSON object.

.. code-block:: php

	$pObj->addToJsonArray('yourKey', $value); // just if you want to add a own key value pair to the existing array.

	// if you want to manipulate the whole aray use the following methods to get the array, modify it like you want and set it again.
	$data = $pObj->getJsonArray();
	//... your changes
	$pObj->setJsonArray($data);

Changing the httpRequest Configuration (TYPO3 7.6)
==================================================

As the extension uses "HttpRequest" class of the TYPO3 core just for TYPO3 7.6, so you have the possibilty to override the given httpRequest Configuration.

For that you can use the signal/slot 

.. code-block:: php

   $signalSlotDispatcher->connect(
      \FORM4\Statusmonitor\Utility\StatusmonitorUtility::class,
      'ChangeHttpRequestConfiguration',
      \YourVendor\YourExtension\YourPath\YourClass::class,
      'NameOfYOurMethod' 
   );
   
   // if you want to change the httpRequestMethod from the default "POST" you can use e.G. from the parent object:
   
   $pObj->setHttpRequestMethod(\TYPO3\CMS\Core\Http\HttpRequest::METHOD_GET) 
   // see HTTP_Request2 for allowed Methods
   
   // if you want to add your own config to the configuration array you have the following Methods in the parent object to set the configuration array:
   
   $pObj->getHttpRequestConfig();
   $pObj->setHttpRequestConfig($httpRequestConfig);
   $pObj->addToHttpRequestConfig($key, $value);
   
   //see \TYPO3\CMS\Core\Http\HttpRequest->setConfiguration() for allowed configuration keys/values

Changing the Request Factory Options Configuration (TYPO3 8.7)
==============================================================

The usage of the HttpRequest Class has been replaced with beginning from TYPO3 8.7, so that here it is necessary to override the options of the "RequestFactory" 
class of the TYPO3 core for TYPO3 8.7. But you also have the possibilty to override the given httpRequest Configuration, here.

For that exists the following signal/slot that has familiar methods like the ones for the httpRequest class. 

.. code-block:: php

   $signalSlotDispatcher->connect(
      \FORM4\Statusmonitor\Utility\StatusmonitorUtility::class,
      'ChangeRequestFactoryOptions',
      \YourVendor\YourExtension\YourPath\YourClass::class,
      'NameOfYOurMethod' 
   );
    
    //POST, GET   
    $pObj->setRequestFactoryMethod($method)
    
    //getting & settings the options
    //for the options compare http://docs.guzzlephp.org/en/latest/request-options.html 
    // and for further informations please consider https://docs.typo3.org/typo3cms/CoreApiReference/ApiOverview/Http/Index.html
    
    $pObj->getRequestFactoryOptions()
    $pObj->setRequestFactoryOptions($options)
    $pObj->addToRequestFactoryOptions($key, $value)
   
