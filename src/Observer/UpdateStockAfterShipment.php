<?php

namespace Scriptbaker\ShipOutofStockItems\Observer;

use Magento\CatalogInventory\Api\StockStateInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\InventoryApi\Api\SourceItemsSaveInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory;
use Magento\InventorySalesApi\Api\GetProductSalableQtyInterface;
use Magento\InventoryCatalogApi\Api\DefaultSourceProviderInterface;
use Psr\Log\LoggerInterface;

class UpdateStockAfterShipment implements ObserverInterface
{
    protected $sourceItemsSaveInterface;
    protected $sourceItemFactory;
    protected $stockState;
    protected $defaultSourceProvider;
    protected $logger;

    public function __construct(
		StockStateInterface $stockState,
        SourceItemsSaveInterface $sourceItemsSaveInterface,
        SourceItemInterfaceFactory $sourceItemFactory,
		DefaultSourceProviderInterface $defaultSourceProvider,
        LoggerInterface $logger
    ) {
        $this->sourceItemsSaveInterface = $sourceItemsSaveInterface;
        $this->sourceItemFactory = $sourceItemFactory;
        $this->stockState = $stockState;
        $this->defaultSourceProvider = $defaultSourceProvider;
        $this->logger = $logger;
    }

    public function execute(Observer $observer)
    {
        try {
            $shipment = $observer->getEvent()->getShipment();
            $items = $shipment->getAllItems();

            if ($items) {
                foreach ($items as $item) {
                    $sku = $item->getSku();
                    $shippedQty = $item->getQty();

                    // Get the current salable quantity
                    $sourceCode = $this->defaultSourceProvider->getCode();
					$productId = $item->getProductId();
					$salableQty = $this->stockState->getStockQty($productId);
                   

                    // Create a source item and update its quantity
                    $sourceItem = $this->sourceItemFactory->create();
                    $sourceItem->setSourceCode($sourceCode);
                    $sourceItem->setSku($sku);
                    $sourceItem->setQuantity($salableQty - $shippedQty);
                    $sourceItem->setStatus(1); // In stock

                    // Save the updated source item
                    $this->sourceItemsSaveInterface->execute([$sourceItem]);
                }
            }
        } catch (\Exception $e) {
            $this->logger->critical('Error updating stock after shipment: ' . $e->getMessage());
        }
    }
}
