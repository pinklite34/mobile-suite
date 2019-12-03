<?php
/*
This file should be used by your integration (most likely a Mobile implementation) to poll the status
of the invoice, without having to constantly poll BitPay and be subject to rate limits or throttling.  

The ipn.php file will be used by BitPay to update / verify the status, and this file can be used to verify that change.
*/
#autoload the classes
function BPC_autoloader($class)
{
    if (strpos($class, 'BPC_') !== false):
        if (!class_exists('BitPayLib/' . $class, false)):
            #doesnt exist so include it
            include 'BitPayLib/' . $class . '.php';
        endif;
    endif;
}
spl_autoload_register('BPC_autoloader');

//sample incoming check
/*
{
"data": {
    "id": "<invoice id>"
	}
}
*/
// modify the following to meet your requirements, this example takes an incoming json post
// Access the incoming data
$json = file_get_contents('php://input');
// decodes to object
$data = json_decode($json);
$invoice_data = $data->data;
#get the value from  your database
include 'conn.php';
$table = '_bitpay_transactions';
$stmt = $mysqli->prepare("SELECT * FROM $table WHERE invoice_id = ?");
$stmt->bind_param("s", $invoice_data->id);
$stmt->execute();
$result = $stmt->get_result();
$row = mysqli_fetch_object($result);
$stmt->close();

/*sample response from the database
{
id: 1,
invoice_id: "<invoice id>",
invoice_status: "paid",
date_added: "2019-12-02 09:55:06"
}
*/
echo json_encode($row);
#your integration should handle the invoice_status returned
 /*
    new - invoice created not paid
    paid - invoice paid but not confirmed.  Best used when a payment is received.  Best for UX, but physical goods should not be shipped
    until a confirmed status is reached
    confirmed - confirmed by 6 blockchain verifications.  This is best used when there are physical good
    expired - invoice has not been paid within 15 miinutes of creation
*/