<?php

namespace Harriswebworks\Pdfmaker\Controller\Index;

use Mpdf\QrCode\QrCode;
use Mpdf\QrCode\Output;
use Picqer\Barcode;

class Index extends \Magento\Framework\App\Action\Action {

    protected $_pageFactory;

    public function __construct(
            \Magento\Framework\App\Action\Context $context,
            \Magento\Framework\View\Result\PageFactory $pageFactory) {
        $this->_pageFactory = $pageFactory;
        return parent::__construct($context);
    }

    public function execute() {

        $id = '000000004';

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $orders = $objectManager->create('Magento\Sales\Model\Order')->load($id);
        $shippingaddress = $orders->getShippingAddress()->getData();
        $items = $orders->getAllItems();
        $product = '';
        foreach ($items as $item) {
            $product .= '<tr>' . '<td>' . $item->getName() . '</td>' . '<td>' . $item->getSku() . '</td>' . '<td>' . $item->getQtyOrdered() . '</td></tr>';
        }
        //print_r($items);
        //print_r($orders->getData());
        //print_r($shippingaddress);
        //echo $custLastName= $orders->getCustomerLastname();
        //$mpdf = new \Mpdf\Mpdf();
        //$code = new \Mpdf\QrCode\QrCode('LOREM IPSUM 2019');
        //$output = new \Mpdf\QrCode\Output\Html();
        //echo $output->output($code);
        $generator = new \Picqer\Barcode\BarcodeGeneratorHTML();
        $barcode = $generator->getBarcode('FMT-00219', $generator::TYPE_CODE_128);
        // print_r(get_class_methods('Magento\Sales\Model\Order'));
        // $code = '';
        //Here Html code
        $html = '
<html>
<head>
<style>
body {font-family: sans-serif;
	font-size: 9pt;

}
h5, p {	margin: 0pt;
}
table.items {
	font-size: 9pt;
	border-collapse: collapse;
	border:0;

}
td { vertical-align: top;border:0;
}
table thead td {
	text-align: left;
	border:0;
	border-top: 3px solid #000000;
	border-bottom: 3px solid #000000;
}
table tfoot td {
	text-align: left;
}
.barcode {

	margin: 0;
	vertical-align: top;
	color: #000000;
}
.barcodecell {

	vertical-align: middle;
	padding: 0;
}
</style>
</head>
<body>

<htmlpagefooter name="myfooter">


<div style="font-size: 16px; padding: 0 !important;">
<div class="barcodecell" style="text-align:left"><barcode code="FMT-00219" type="C128A" class="barcode" /></div>
<div style="padding: 5px 0 0 15px">' . $shippingaddress['firstname'] . " " . $shippingaddress['lastname'] . '</div>
<div style="padding-left: 15px">' . $shippingaddress['company'] . '</div>
<div style="padding-left: 15px">' . $shippingaddress['street'] . '</div>
<div style="padding-left: 15px">' . $shippingaddress['city'] . ", " . $shippingaddress['region'] . ", " . $shippingaddress['postcode'] . '</div>
<div style="padding-left: 15px">#' . $id . '</div>
</div>
</htmlpagefooter>
<sethtmlpagefooter name="myfooter" value="on" />

<div class="barcodecell" style="text-align:right;padding-bottom:10px;"><barcode code="FMT-00219" type="C128A" class="barcode" /></div>
<table class="header" width="100%" cellpadding="0" border="0">
<tr>
<td><img src="https://d3sq1kbqw5tk5l.cloudfront.net/logo/websites/8/maidpro-logo.jpg" ></td>
<td style="border-left:1px solid #000000; padding-left:15px; padding-top:20px">Fulfilled by Darter Specialties<br />
500 Cornwall Avenue<br />
Cheshire, CT 06410<br />
203.699.9805<br />
sales@darterpress.com
</td>
</tr>
</table>
<h2 style="border-top:3px solid #000000; border-bottom:1px solid #000000;padding:5px 0; ">
<span style="display:inline-block;margin-right:30px">Order: #' . $id . '</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span >Order Date: May 8, 2020</span>
</h2>
<div style="padding-left:30px;padding-bottom:15px;">
' . $shippingaddress['firstname'] . " " . $shippingaddress['lastname'] . '<br />
' . $shippingaddress['company'] . '<br />
' . $shippingaddress['street'] . '<br />
' . $shippingaddress['city'] . ", " . $shippingaddress['region'] . ", " . $shippingaddress['postcode'] . '<br />
T: ' . $shippingaddress['telephone'] . '<br />
E: ' . $shippingaddress['email'] . '<br />
</div>
<table class="items" width="100%" cellpadding="8" border="1">
<thead>
<tr>
<td >Product Name</td>
<td>SKU</td>
<td>QTY</td>
</tr>
</thead>
<tbody>
' . $product . '


</tbody>
</table>




</body>
</html>
';

        $mpdf = new \Mpdf\Mpdf([
            'margin_left' => 15,
            'margin_right' => 15,
            'margin_top' => 0,
            'margin_bottom' => 25,
            'margin_header' => 0,
            'margin_footer' => 10,
            'showBarcodeNumbers' => FALSE
        ]);

        $mpdf->WriteHTML($html);
        $filename = 'AllPdf/' . uniqid() . 'test.pdf';
        $mpdf->Output($filename, 'F');
    }

}
