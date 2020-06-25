<?php

namespace Harriswebworks\Pdfmaker\Controller\Adminhtml\Index;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Sales\Api\OrderManagementInterface;

class Index extends \Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction {

    public function __construct(
            Context $context,
            Filter $filter,
            CollectionFactory $collectionFactory,
            OrderManagementInterface $orderManagement
    ) {
        parent::__construct($context, $filter);
        $this->collectionFactory = $collectionFactory;
        $this->orderManagement = $orderManagement;
    }

    protected function massAction(AbstractCollection $collection) {
        $countDeleteOrder = 0;
        //$model = $this->_objectManager->create('Magento\Sales\Model\Order');
        foreach ($collection->getItems() as $order) {
            if (!$order->getEntityId()) {
                continue;
            }
            //$loadedOrder = $model->load($order->getEntityId());
            print_r($loadedOrder);
            //$loadedOrder->delete();
            $countDeleteOrder++;
        }
        exit;


}
}