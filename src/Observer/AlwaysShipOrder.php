<?php

namespace Scriptbaker\ShipOutofStockItems\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\CatalogInventory\Api\StockStateInterface;
use Magento\InventoryApi\Api\SourceItemsSaveInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory;
use Psr\Log\LoggerInterface;

class AlwaysShipOrder implements ObserverInterface
{
    protected $stockState;
    protected $sourceItemsSaveInterface;
    protected $sourceItemFactory;
	protected $logger;
	
    public function __construct(
        StockStateInterface $stockState,
        SourceItemsSaveInterface $sourceItemsSaveInterface,
        SourceItemInterfaceFactory $sourceItemFactory,
        LoggerInterface $logger
    ) {
        $this->stockState = $stockState;
        $this->sourceItemsSaveInterface = $sourceItemsSaveInterface;
        $this->sourceItemFactory = $sourceItemFactory;
		$this->logger = $logger;
    }

    public function execute(Observer $observer)
    {
		try {
			$items = $observer->getEvent()->getShipment()->getAllItems();
			if ($items) {
				foreach ($items as $item) {
					$productId = $item->getProductId();
					$qty = $this->stockState->getStockQty($productId);
					$itemQty = $item->getQty();
					if ($qty < $itemQty) {
						$sourceItem = $this->sourceItemFactory->create();
						$sourceItem->setSourceCode('default');
						$sourceItem->setSku($item->getSku());
						$sourceItem->setQuantity($itemQty - $qty); // Add only the missing quantity
						$sourceItem->setStatus(1);
						$this->sourceItemsSaveInterface->execute([$sourceItem]);
					}
				}
			}
		} catch (\Exception $e) {
            $this->logger->critical('Error updating stock before shipment: ' . $e->getMessage());
        }
    }
}
