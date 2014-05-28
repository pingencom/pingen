<?php

namespace Pingen;

use Buzz\Browser;
use Buzz\Message\Form\FormRequest;
use Buzz\Message\Form\FormUpload;

/**
 * A class to use the API of pingen.com as an integrator (Version 1.05)
 *
 * For more information about Pingen and how to use it as an integrator see
 * https://pingen.com/en/customer/integrator/Briefversand-für-Integratoren.html
 *
 * API documentation can be found here:
 * https://www.pingen.com/en/developer.html
 *
 *
 *  Copyright (c) 2013, Pingen GmbH
 *  All rights reserved.
 *
 *  Redistribution and use in source and binary forms, with or without
 *  modification, are permitted provided that the following conditions are met:
 *   * Redistributions of source code must retain the above copyright
 *  notice, this list of conditions and the following disclaimer.
 *   * Redistributions in binary form must reproduce the above copyright
 *  notice, this list of conditions and the following disclaimer in the
 *  documentation and/or other materials provided with the distribution.
 *   * Neither the name of the <organization> nor the
 *  names of its contributors may be used to endorse or promote products
 *  derived from this software without specific prior written permission.
 *
 *  THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 *  ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 *  WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 *  DISCLAIMED. IN NO EVENT SHALL <COPYRIGHT HOLDER> BE LIABLE FOR ANY
 *  DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 *  (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 *  LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 *  ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 *  (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 *  SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @link https://www.pingen.com/en/developer.html
 */
class Pingen
{
    /**
     * @constant string Production Mode
     */
    const MODE_PRODUCTION = 1;

    /**
     * @constant string Staging/Development Mode
     */
    const MODE_STAGING = 2;

    /**
     * @constant string Library-Version
     */
    const VERSION = 1.05;

    /**
     * @constant string Print in Black & White
     */
    const PRINT_BLACK = 0;

    /**
     * @constant string Print in color
     */
    const PRINT_COLOR = 1;

    /**
     * @constant string Sending speed priority
     */
    const SPEED_PRIORITY = 1;

    /**
     * @constant string Sending speed economy
     */
    const SPEED_ECONOMY = 2;

    /**
     * @var string Base URL of Pingen API
     */
    protected $sBaseURL = '';

    /**
     * @var string Auth token
     */
    private $sToken;

    /**
     * Constructor of class
     *
     * @param string $sToken Auth token
     * @param integer $iMode Production or Staging Environment
     */
    public function __construct($sToken, $iMode = self::MODE_PRODUCTION)
    {
        $this->sToken = $sToken;

        switch($iMode)
        {
            case self::MODE_PRODUCTION:
                $this->sBaseURL = 'https://api.pingen.com';
                break;
            case self::MODE_STAGING:
                $this->sBaseURL = 'https://stage-api.pingen.com';
                break;
            default:
                throw new Exception('The specified mode does not exist');
                break;
        }
    }

    /**
     * You can list your available documents
     *
     * See https://www.pingen.com/en/developer/endpoints-documents.html
     *
     * @param int $iLimit Limit the amount of results
     * @param int $iPage When limiting the results, specifies page
     * @param string $sSort Sorts the list by the available values
     * @param string $sSortType Defines the way of sorting
     * @param string $aFilter Set of filters for list
     * @return object
     */
    public function document_list($iLimit = 0, $iPage = 1, $sSort = 'date', $sSortType = 'desc', $aFilter = array())
    {
        return $this->execute('GET', "document/list/limit/$iLimit/page/$iPage/sort/$sSort/sorttype/$sSortType" . $this->parse_filters($aFilter));
    }

    /**
     * Get information about a specific document
     *
     * See https://www.pingen.com/en/developer/endpoints-documents.html for available options
     *
     * @param int $iDocumentId
     * @return object
     */
    public function document_get($iDocumentId)
    {
        return $this->execute('GET', "document/get/id/$iDocumentId");
    }

    /**
     * Download a specific document as pdf
     *
     * See https://www.pingen.com/en/developer/endpoints-documents.html for available options
     *
     * @param int $iDocumentId
     * @return application/pdf
     */
    public function document_pdf($iDocumentId)
    {
        return $this->execute('GET', "document/pdf/id/$iDocumentId");
    }

    /**
     * Preview a specific document as png
     *
     * See https://www.pingen.com/en/developer/endpoints-documents.html for available options
     *
     * @param int $iDocumentId Document id
     * @param int $iPage Number of page that will be grabbed
     * @param int $iSize Withd of preview
     * @return image/png
     */
    public function document_preview($iDocumentId, $iPage = 1, $iSize = 595)
    {
        return $this->execute('GET', "document/preview/id/$iDocumentId/page/$iPage/size/$iSize");
    }

    /**
     * Delete a specific document
     *
     * @param int $iDocumentId
     * @return object
     */
    public function document_delete($iDocumentId)
    {
        return $this->execute('POST', "document/delete/id/$iDocumentId");
    }

    /**
     * Send a specific document
     *
     * See https://www.pingen.com/en/developer/endpoints-documents.html for available options
     *
     * @param int $iDocumentId
     * @param int $iSpeed
     * @param int $iColor
     * @return object
     */
    public function document_send($iDocumentId, $iSpeed = self::SPEED_PRIORITY, $iColor = self::PRINT_COLOR)
    {
        $aData = array('speed' => $iSpeed, 'color' => $iColor);
        return $this->execute('POST', "document/send/id/$iDocumentId", $aData);
    }

    /**
     * Upload a new file (and optionally send it right away)
     *
     * See https://www.pingen.com/en/developer/endpoints-documents.html for available options
     *
     * @param string $sFile
     * @param int $iSend
     * @param int $iSpeed
     * @param int $iColor
     * @return object
     */
    public function document_upload($sFile, $iSend = 0, $iSpeed = self::SPEED_PRIORITY, $iColor = self::PRINT_COLOR)
    {
        $aOptions = array('send' => $iSend, 'speed' => $iSpeed, 'color' => $iColor);
        return $this->execute('POST', 'document/upload', $aOptions, $sFile);
    }

    /**
     * You can list your available letters
     *
     * See https://www.pingen.com/en/developer/endpoints-letters.html
     *
     * @param int $iLimit Limit the amount of results
     * @param int $iPage When limiting the results, specifies page
     * @param string $sSort Sorts the list by the available values
     * @param string $sSortType Defines the way of sorting
     * @param string $aFilter Set of filters for list
     * @return object
     */
    public function letter_list($iLimit = 0, $iPage = 1, $sSort = 'date', $sSortType = 'desc', $aFilter = array())
    {
        return $this->execute('GET', "letter/list/limit/$iLimit/page/$iPage/sort/$sSort/sorttype/$sSortType" . $this->parse_filters($aFilter));
    }

    /**
     * You can get your letter object
     *
     * See https://www.pingen.com/en/developer/endpoints-letters.html
     *
     * @param int $iLetterId The Id of the letter
     * @return object
     */
    public function letter_get($iLetterId)
    {
        return $this->execute('GET', "letter/get/id/$iLetterId");
    }

    /**
     * You can add new letter
     *
     * See https://www.pingen.com/en/developer/endpoints-letters.html
     *
     * @param array $aData Body parameters
     * @return object
     */
    public function letter_add($aData)
    {
        return $this->execute('POST', "letter/add", $aData);
    }

    /**
     * You can edit letter
     *
     * See https://www.pingen.com/en/developer/endpoints-letters.html
     *
     * @param int $iLetterId The id of the letter
     * @param array $aData Body Parameters
     * @return object
     */
    public function letter_edit($iLetterId, $aData)
    {
        return $this->execute('POST', "letter/edit/id/$iLetterId", $aData);
    }

    /**
     * You can get letter preview
     *
     * See https://www.pingen.com/en/developer/endpoints-letters.html
     *
     * @param int $iLetterId The id of the letter
     * @param int $iPage The page of the letter to grab as preview
     * @param int $iSize The width of preview
     * @return application/image
     */
    public function letter_preview($iLetterId, $iPage = 1, $iSize = 595)
    {
        return $this->execute('GET', "letter/preview/id/$iLetterId/page/$iPage/size/$iSize");
    }

    /**
     * You can get letter as pdf
     *
     * See https://www.pingen.com/en/developer/endpoints-letters.html
     *
     * @param int $iLetterId The id of the letter
     * @return application/pdf
     */
    public function letter_pdf($iLetterId)
    {
        return $this->execute('GET', "letter/pdf/id/$iLetterId");
    }

    /**
     * You can send letter
     *
     * See https://www.pingen.com/en/developer/endpoints-letters.html
     *
     * @param int $iLetterId The id of the letter
     * @param int $iSpeed
     * @param int $iColor
     * @return object
     */
    public function letter_send($iLetterId, $iSpeed = self::SPEED_PRIORITY, $iColor = self::PRINT_COLOR)
    {
        $aData = array('speed' => $iSpeed, 'color' => $iColor);
        return $this->execute('POST', "letter/send/id/$iLetterId", $aData);
    }

    /**
     * You can delete letter
     *
     * See https://www.pingen.com/en/developer/endpoints-letters.html
     *
     * @param int $iLetterId The id of the letter
     * @return object
     */
    public function letter_delete($iLetterId)
    {
        return $this->execute('POST', "letter/delete/id/$iLetterId");
    }

    /**
     * You can list your available post sends
     *
     * See https://www.pingen.com/en/developer/endpoints-send.html
     *
     * @param int $iLimit Limit the amount of results
     * @param int $iPage When limiting the results, specifies page
     * @param string $sSort Sorts the list by available values
     * @param string $sSortType Defines the way of sorting
     * @param string $aFilter Set of filters for list
     * @return object
     */
    public function send_list($iLimit = 0, $iPage = 1, $sSort = 'date', $sSortType = 'desc', $aFilter = array())
    {
        return $this->execute('GET', "send/list/limit/$iLimit/page/$iPage/sort/$sSort/sorttype/$sSortType" . $this->parse_filters($aFilter));
    }

    /**
     * You can get your send object
     *
     * See https://www.pingen.com/en/developer/endpoints-send.html
     *
     * @param int $iSendId The Id of the post sending
     * @return object
     */
    public function send_get($iSendId)
    {
        return $this->execute('GET', "send/get/id/$iSendId");
    }

    /**
     * Retreive your send confirmation document
     *
     * See https://www.pingen.com/en/developer/endpoints-send.html
     *
     * @param int $iSendId The Id of the post sending
     * @return object
     */
    public function send_confirmation($iSendId)
    {
        return $this->execute('GET', "send/confirmation/id/$iSendId");
    }

    /**
     * Cancel your sending
     *
     * See https://www.pingen.com/en/developer/endpoints-send.html
     *
     * @param int $iSendId The Id of the post sending
     * @return object
     */
    public function send_cancel($iSendId)
    {
        return $this->execute('GET', "send/cancel/id/$iSendId");
    }

    /**
     * Track your sending if possible
     *
     * See https://www.pingen.com/en/developer/endpoints-send.html
     *
     * @param int $iSendId The Id of the post sending
     * @return object
     */
    public function send_track($iSendId)
    {
        return $this->execute('GET', "send/track/id/$iSendId");
    }

    /**
     * Get the available speeds for a country or list of countries
     *
     * See https://www.pingen.com/en/developer/endpoints-send.html
     *
     * @param mixed $mCountries The country (ISO2) or array of countries
     * @return object
     */
    public function send_speed($mCountries)
    {
        if (!is_array($mCountries))
        {
            $mCountries = array($mCountries);
        }
        return $this->execute('GET', "send/speed/countries/" . implode(',', $mCountries));
    }

    /**
     * You can list your queue
     *
     * See https://www.pingen.com/en/developer/endpoints-queue.html
     *
     * @param int $iLimit Limit the amount of results
     * @param int $iPage When limiting the results, specifies page
     * @param string $sSort Sorts the list by available values
     * @param string $sSortType Defines the way of sorting
     * @param string $aFilter Set of filters for list
     * @return object
     */
    public function queue_list($iLimit = 0, $iPage = 1, $sSort = 'date', $sSortType = 'desc', $aFilter = array())
    {
        return $this->execute('GET', "queue/list/limit/$iLimit/page/$iPage/sort/$sSort/sorttype/$sSortType" . $this->parse_filters($aFilter));
    }

    /**
     * You can get your queue
     *
     * See https://www.pingen.com/en/developer/endpoints-queue.html
     *
     * @param int $iQueueId The Id of the queue entry
     * @return object
     */
    public function queue_get($iQueueId)
    {
        return $this->execute('GET', "queue/get/id/$iQueueId");
    }

    /**
     * You can cancel a pending queue entry
     *
     * See https://www.pingen.com/en/developer/endpoints-queue.html
     *
     * @param int $iQueueId The Id of the queue entry
     * @param array $aData Body Parameters
     * @return object
     */
    public function queue_cancel($iQueueId, $aData = array())
    {
        return $this->execute('POST', "queue/cancel/id/$iQueueId", $aData);
    }

    /**
     * You can list your available contacts
     *
     * See https://www.pingen.com/en/developer/endpoints-contacts.html
     *
     * @param int $iLimit Limit the amount of results
     * @param int $iPage When limiting the results, specifies page
     * @param string $sSort Sorts the list by available values
     * @param string $sSortType Defines the way of sorting
     * @param string $aFilter Set of filters for list
     * @return object
     */
    public function contact_list($iLimit = 0, $iPage = 1, $sSort = 'id', $sSortType = 'desc', $aFilter = array())
    {
        return $this->execute('GET', "contact/list/limit/$iLimit/page/$iPage/sort/$sSort/sorttype/$sSortType" . $this->parse_filters($aFilter));
    }

    /**
     * You can get your document
     *
     * See https://www.pingen.com/en/developer/endpoints-contacts.html
     *
     * @param int $iContactId The Id of the contact
     * @return object
     */
    public function contact_get($iContactId)
    {
        return $this->execute('GET', "contact/get/id/$iContactId");
    }

    /**
     * You can add new contact
     *
     * See https://www.pingen.com/en/developer/endpoints-contacts.html
     *
     * @param array $aData Body parameters
     * @return object
     */
    public function contact_add($aData)
    {
        return $this->execute('POST', "contact/add", $aData);
    }

    /**
     * You can edit new contact
     *
     * See https://www.pingen.com/en/developer/endpoints-contacts.html
     *
     * @param int $iContactId The Id of the contact
     * @param array $aData Body parameters
     * @return object
     */
    public function contact_edit($iContactId, $aData)
    {
        return $this->execute('POST', "contact/edit/id/$iContactId", $aData);
    }

    /**
     * You can delete a contact
     *
     * See https://www.pingen.com/en/developer/endpoints-contacts.html
     *
     * @param int $iContactId The Id of the contact
     * @return object
     */
    public function contact_delete($iContactId)
    {
        return $this->execute('POST', "contact/delete/id/$iContactId");
    }

    /**
     * You can calculate fax sending
     *
     * @param string $sNumber Fax number starting with country code and plus at beginning
     * @param int $iPages Number of pages per document
     * @param int $iDocuments Number of documents
     * @param string $sCurrency Currency of calculation
     * @return object
     */
    public function calculator_fax($sNumber, $iPages = 1, $iDocuments = 1, $sCurrency = 'CHF')
    {
        return $this->execute('GET', "calculator/fax/number/" . urlencode($sNumber) . "/pages/$iPages/documents/$iDocuments/currency/$sCurrency");
    }

    /**
     * You can calculate post sending
     *
     * @param string $sCountry Country code for sending
     * @param int $iSpeed Speed option for normal/express
     * @param int $iPrint Print option for black/color
     * @param int $iDocuments Number of documents
     * @param int $iPagesNormal Number of normal pages
     * @param int $iPagesESR Number of ESR pages
     * @param int $iPlan Your plan
     * @param string $sCurrency Currency of payment
     * @return object
     */
    public function calculator_post($sCountry = 'CH', $iSpeed = self::SPEED_PRIORITY, $iPrint = self::PRINT_COLOR, $iDocuments = 1, $iPagesNormal = 1, $iPagesESR = 0, $iPlan = 1, $sCurrency = 'CHF')
    {
        return $this->execute('GET', "calculator/get/country/$sCountry/print/$iPrint/speed/$iSpeed/plan/$iPlan/documents/$iDocuments/currency/$sCurrency/pages_normal/$iPagesNormal/pages_esr/$iPagesESR");
    }

    /**
     * Grabbing your current credit value
     *
     * @return object
     */
    public function account_credit()
    {
        return $this->execute('GET', "account/credit");
    }

    /**
     * Grabbing your actual plan
     *
     * @return object
     */
    public function account_plan()
    {
        return $this->execute('GET', "account/plan");
    }

    /**
     * @param string $sKeyword
     * @param array $aBodyParameters
     * @param string $sFile
     *
     * @return object
     */
    private function execute($method, $sKeyword, $aBodyParameters = array(), $sFile = false)
    {
        // prepare url
        $aURLParts = array(
            $this->sBaseURL,
            $sKeyword,
            'token',
            $this->sToken
        );
        $sURL = implode('/', $aURLParts);

        $browser = new Browser();

        // handle file uploads
        if ($sFile) {
            $upload = new FormUpload();
            $upload->setFilename(basename($sFile));
            $upload->setContent(file_get_contents($sFile));

            $request = new FormRequest();
            $request->setField('file', $upload);
            $request->setField('data', json_encode($aBodyParameters));

            $mResponse = $browser->post($sURL, $request->getHeaders(), $request->getContent());

        // handle all other requests
        } else {
            switch ($method) {
                case 'GET':
                    $mResponse = $browser->get($sURL);
                    break;
                case 'POST':
                    $mResponse = $browser->post($sURL, array(), json_encode($aBodyParameters));
                    break;
            }
        }

        //handle response
        $objResponse = json_decode($mResponse->getContent());
        if (property_exists($objResponse, 'error') && $objResponse->error)
        {
            throw new \Exception($objResponse->errormessage, $objResponse->errorcode);
        }

        return $objResponse;

    }

    private function parse_filters($aFilters)
    {
        $aSets = array();

        foreach ($aFilters as $sFilter => $sValue)
        {
            $aSets[] = "{$sFilter}:{$sValue}";
        }

        $sFilter = implode(';', $aSets);

        if ($sFilter)
        {
            return "/filter/$sFilter";
        }
        else
        {
            return '';
        }
    }
}