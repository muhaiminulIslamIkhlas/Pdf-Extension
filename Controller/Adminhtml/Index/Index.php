<?php

namespace Harriswebworks\Pdfmaker\Controller\Adminhtml\Index;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Mpdf\QrCode\QrCode;
use Mpdf\QrCode\Output;
use Picqer\Barcode;

class Index extends \Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction {

    protected $messageManager;
    protected $resultRedirectFactory;
    protected $magentoOrder;

    public function __construct(
            \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory,
            Context $context, Filter $filter,
            CollectionFactory $collectionFactory,
            \Magento\Framework\Message\ManagerInterface $messageManager,
            \Magento\Sales\Model\Order $magentoOrder
    ) {
        parent::__construct($context, $filter);
        $this->collectionFactory = $collectionFactory;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->messageManager = $messageManager;
        $this->magentoOrder = $magentoOrder;
    }

    public function getItemOptions($item) {
        $result = [];
        $options = $item->getProductOptions();
        if ($options) {
            if (isset($options['attributes_info'])) {
                $result = array_merge($result, $options['attributes_info']);
            }
            if (isset($options['options'])) {
                $result = array_merge($result, $options['options']);
            }
            if (isset($options['additional_options'])) {
                $result = array_merge($result, $options['additional_options']);
            }
        }
        return $result;
    }

    public function pdfGenerate($id) {


        $orders = $this->magentoOrder->load($id);
        $shippingaddress = $orders->getShippingAddress()->getData();
        $orderItems = $orders->getAllVisibleItems();
        $orderId = $orders->getRealOrderId();
        // $orderDate=date("M d Y", $orders->getCreatedAt());
        $time = strtotime($orders->getCreatedAt());
        $orderDate = date("M d, Y", $time);
        $count = 0;
        $product = '';
        $product_second = '';
        foreach ($orderItems as $item) {
            $count += 1;

            if ($count > 7) {
                $optionhtml = '';
                $options = $this->getItemOptions($item);
                if (!empty($options)) {
                    $optionhtml = '<div class="item-options">';
                    foreach ($options as $option) {
                        $optionhtml .= '<div class="option">';
                        $optionhtml .= '<span>' . $option['label'] . ' :</span>';
                        $optionhtml .= '<strong> ' . nl2br($option['value']) . '</strong>';
                        $optionhtml .= '</div>';
                    }
                    $optionhtml .= '</div>';
                }
                $product_second .= '<tr>' . '<td>' . $item->getName() . $optionhtml . '</td>' . '<td>' . $item->getSku() . '</td>' . '<td>' . round($item->getQtyOrdered()) . '</td></tr>';
            } else {
                $optionhtml = '';
                $options = $this->getItemOptions($item);
                if (!empty($options)) {
                    $optionhtml = '<div class="item-options">';
                    foreach ($options as $option) {
                        $optionhtml .= '<div class="option">';
                        $optionhtml .= '<span>' . $option['label'] . ' :</span>';
                        $optionhtml .= '<strong> ' . nl2br($option['value']) . '</strong>';
                        $optionhtml .= '</div>';
                    }
                    $optionhtml .= '</div>';
                }
                $product .= '<tr>' . '<td>' . $item->getName() . $optionhtml . '</td>' . '<td>' . $item->getSku() . '</td>' . '<td>' . round($item->getQtyOrdered()) . '</td></tr>';
            }
//            $optionhtml = '';
//            $options = $this->getItemOptions($item);
//            if (!empty($options)) {
//                $optionhtml = '<div class="item-options">';
//                foreach ($options as $option) {
//                    $optionhtml .= '<div class="option">';
//                    $optionhtml .= '<span>' . $option['label'] . ' :</span>';
//                    $optionhtml .= '<strong> ' . nl2br($option['value']) . '</strong>';
//                    $optionhtml .= '</div>';
//                }
//                $optionhtml .= '</div>';
//            }
//            $product .= '<tr>' . '<td>' . $item->getName() . $optionhtml . '</td>' . '<td>' . $item->getSku() . '</td>' . '<td>' . round($item->getQtyOrdered()) . '</td></tr>';
        }
        $generator = new \Picqer\Barcode\BarcodeGeneratorHTML();
        $barcode = $generator->getBarcode('FMT-00219', $generator::TYPE_CODE_128);
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
<div class="barcodecell" style="text-align:left"><barcode code="' . $orderId . '" type="C128B" class="barcode" /></div>
<div style="padding: 5px 0 0 15px">' . $shippingaddress['firstname'] . " " . $shippingaddress['lastname'] . '</div>
<div style="padding-left: 15px">' . $shippingaddress['company'] . '</div>
<div style="padding-left: 15px">' . $shippingaddress['street'] . '</div>
<div style="padding-left: 15px">' . $shippingaddress['city'] . ", " . $shippingaddress['region'] . ", " . $shippingaddress['postcode'] . '</div>
<div style="padding-left: 15px">#' . $orderId . '</div>
</div>
</htmlpagefooter>
<sethtmlpagefooter name="myfooter" value="on" />

<div class="barcodecell" style="text-align:right;padding-bottom:10px;"><barcode code="' . $orderId . '" type="C128B" class="barcode" /></div>
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
<h2 style="border-top:3px solid #000000; border-bottom:1px solid #000000;padding:5px 0;margin:2 ">
<span style="display:inline-block;margin-right:30px">Order: #' . $orderId . '</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span >Order Date: ' . $orderDate . '</span>
</h2>
<div style="padding-left:30px;">
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

        $html2 = '<html>
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


<h2 style="border-top:3px solid #000000; border-bottom:1px solid #000000;padding:5px 0;margin:2 ">
<span style="display:inline-block;margin-right:30px">Order: #' . $orderId . '</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span >Order Date: ' . $orderDate . '</span>
</h2>
<div style="padding-left:30px;">
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
' . $product_second . '


</tbody>
</table>




</body>
</html>';


        $header = '<h2 style="border-top:3px solid #000000; border-bottom:1px solid #000000;padding:5px 0;margin:2 ">
<span style="display:inline-block;margin-right:30px">Order: #' . $orderId . '</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span >Order Date: ' . $orderDate . '</span>
</h2>
<div style="padding-left:30px;">
' . $shippingaddress['firstname'] . " " . $shippingaddress['lastname'] . '<br />
' . $shippingaddress['company'] . '<br />
' . $shippingaddress['street'] . '<br />
' . $shippingaddress['city'] . ", " . $shippingaddress['region'] . ", " . $shippingaddress['postcode'] . '<br />
T: ' . $shippingaddress['telephone'] . '<br />
E: ' . $shippingaddress['email'] . '<br />
</div>';



        // $mpdf->SetFooter($footer);
//        $filename = '' . date("Y-m-d-H-i-s") . '_order_id_.pdf';
//        return $mpdf->Output($filename, 'D');

        $body = array(
            "html" => $html,
            "html2" => $html2,
            "count" => $count,
            "header" => $header
        );

        return $body;



//echo $html;exit;
        //return $html;
    }

    protected function massAction(AbstractCollection $collection) {
        $orderIds = $collection->getAllIds(); // Get the selected orders
        $data = array();
        foreach ($orderIds as $id) {
            $data[] = $this->pdfGenerate($id);
        }

        




//        
//        $mpdf = new \Mpdf\Mpdf([
//            'margin_left' => 15,
//            'margin_right' => 15,
//            'margin_top' => 0,
//            'margin_bottom' => 15,
//            'margin_header' => 0,
//            'margin_footer' => 26,
//            'showBarcodeNumbers' => FALSE
//        ]);
//        $count=count($data);
//        $i=1;
//        foreach($data as $item){
//            $mpdf->WriteHTML($item);
//            if($i!=$count){
//                $mpdf->AddPage();
//            }
//            $i=$i+1;
//            
//        }




        /* New work */
        $count=count($data);
        $i=1;
        $mpdf = new \Mpdf\Mpdf([
            'margin_left' => 15,
            'margin_right' => 15,
            'margin_top' => 0,
            'margin_bottom' => 15,
            'margin_header' => 0,
            'margin_footer' => 26,
            'showBarcodeNumbers' => FALSE
        ]);

        foreach ($data as $item) {
            $mpdf->WriteHTML($item['html']);
            if ($item['count'] > 7) {
                $mpdf->AddPage();
                $mpdf->SetHTMLHeader($item['header']);
                $mpdf->SetHtmlFooter('  ');
                $mpdf->WriteHTML($item['html2']);
                $mpdf->SetHTMLHeader('  ');
            }
            if($i!=$count){
                $mpdf->AddPage();
                $mpdf->SetHTMLHeader('  ');
            }
            $i=$i+1;
        }
        
        $filename = '' . date("Y-m-d-H-i-s") . '_order_id_.pdf';
        return $mpdf->Output($filename, 'D');
        
        exit;







        /* -----New work----- */



        $mpdf = new \Mpdf\Mpdf();


//        $mpdf->defHTMLHeaderByName(
//                'myHeader2',
//                '<div style="text-align: center; font-weight: bold;">Chapter 2</div>'
//        );
//         $mpdf->defHTMLFooterByName(
//                'myFooter2',
//                '<div style="text-align: center; font-weight: bold;">Chapter 2</div>'
//        );
        $cabecalho = '<div>header</div>';
        $footer = "<table name='footer' width=\"1000\">
           <tr>
             <td style='font-size: 18px; padding-bottom: 20px;' align=\"right\">Hello world</td>
           </tr>
         </table>";

        $mpdf->WriteHTML('Your Introduction');
        $mpdf->SetFooter($footer);

// Selects new headers for ODD and EVEN pages to use from the new page onwards
// Note the html_ prefix before the named HTML header
        $mpdf->AddPage();
        $cabecalhoo = '<div>Header 2</div>';
        $footerr = "<table name='footer' width=\"1000\">
           <tr>
             <td style='font-size: 18px; padding-bottom: 20px;' align=\"right\">Hello world 2</td>
           </tr>
         </table>";
        $mpdf->SetHTMLHeader($cabecalhoo);

        $mpdf->WriteHTML('Your Book text');
        $mpdf->SetHtmlFooter('  ');
// Turns all headers/footers off from new page onwards
        $mpdf->AddPage();
        $mpdf->WriteHTML('End section of book with no headers');









        $filename = '' . date("Y-m-d-H-i-s") . '_order_id_.pdf';
        return $mpdf->Output($filename, 'D');
    }

}
