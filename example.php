<?php

//@fixme: The code below is not up-to-date as example.pdf does not match the sending criterias.
//@fixme: The staging api seems not to be accepting the access token. Or maybe the documentation is unclear about what token to use in staging env?

include "vendor/autoload.php";
use \Pingen\Pingen as Pingen;

$objPingen = new \Pingen\Pingen('mytoken');

try {
    /* example uploading a document, and automatically sending it priority and in color */
    $objResponse = $objPingen->send_list('example.pdf', true, Pingen::SPEED_PRIORITY, Pingen::PRINT_COLOR);

    /* grab send/post id */
    $iSendId = $objResponse->send[0]->send_id;

    /* check status of my sending */
    $objSend = $objPingen->send_get($iSendId);

    /*
       to see all status codes go to:
       https://www.pingen.com/en/developer/objects-post.html
    */
    $iStatusCode = $objSend->item->status;

//    /* example of adding a letter */
//    $objLetterResponse = $objPingen->letter_add(array(
//        'recipients' => array(
//            array(
//                'name' => 'David Peterson',
//                'address' => "Longstreet 4\n8005 Zürich\nSwitzerland"
//            ),
//        ),
//        'place' => 'Zürich',
//        'date' => '2013-03-20',
//        'title' => 'Testdocument',
//        'content' => "This is my multiline content<br>fully <i>capable</i> of <b>html</b>"
//    ));
//
//    /* grab letter id just created */
//    $iLetterId = $objLetterResponse->id;
//
//    /* sending this letter */
//    $objSendLetterResponse = $objPingen->letter_send($iLetterId, 1, 1);
//
//    $iSendId = $objSendLetterResponse->id;

}catch (Exception $e)
{
    echo 'An error occured with number <b>' . $e->getCode() . '</b> and message <b>' . $e->getMessage() . '</b>';
}