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

    public function pdfGenerate($id) {

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $orders=$this->magentoOrder->load($id);
        $shippingaddress = $orders->getShippingAddress()->getData();
        $orderItems = $orders->getAllItems();
        $orderId = $orders->getRealOrderId();

        $product = '';
        foreach ($orderItems as $item) {
            $product .= '<tr>' . '<td>' . $item->getName() . '</td>' . '<td>' . $item->getSku() . '</td>' . '<td>' . $item->getQtyOrdered() . '</td></tr>';
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
<div class="barcodecell" style="text-align:left"><barcode code="FMT-00219" type="C128A" class="barcode" /></div>
<div style="padding: 5px 0 0 15px">' . $shippingaddress['firstname'] . " " . $shippingaddress['lastname'] . '</div>
<div style="padding-left: 15px">' . $shippingaddress['company'] . '</div>
<div style="padding-left: 15px">' . $shippingaddress['street'] . '</div>
<div style="padding-left: 15px">' . $shippingaddress['city'] . ", " . $shippingaddress['region'] . ", " . $shippingaddress['postcode'] . '</div>
<div style="padding-left: 15px">#' . $orderId . '</div>
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
<span style="display:inline-block;margin-right:30px">Order: #' . $orderId . '</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span >Order Date: May 8, 2020</span>
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
        $filename = 'var/export/' . uniqid() . '_order_id_' . $orderId . '.pdf';
        $mpdf->Output($filename, 'F');
        $this->messageManager->addSuccess('Invoice generated for order #' . $orderId . ' ');
    }

    protected function massAction(AbstractCollection $collection) {
        $orderIds = $collection->getAllIds(); // Get the selected orders
         foreach($orderIds as $id){
          $this->pdfGenerate($id);
          } 
        $resultRedirect = $this->resultRedirectFactory->create();
                
        $resultRedirect->setPath('http://myshop.dev/admin/sales/order/');
                
        return $resultRedirect;

     
    }

}
