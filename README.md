# Scriptbaker_ShipOutOfStockItems

## Overview

The `Scriptbaker_ShipOutOfStockItems` module for Magento 2 allows the creation of shipments for orders containing items that are out of stock. In default Magento, attempting to create a shipment for an order with out-of-stock items results in the error: "Not all of your products are available in the requested quantity." This module remedies that problem by adjusting the stock quantities to ensure the shipment can be processed.

## Features

- Enables shipment creation for out-of-stock items.
- Automatically adjusts stock quantities to allow for shipment processing.
- Seamlessly integrates with Magento's Multi-Source Inventory (MSI) system.

## Installation

1. **Download the Module:**
   Download the module package or clone the repository into the `app/code/Scriptbaker/ShipOutOfStockItems` directory.

2. **Enable the Module:**
   ```sh
   bin/magento module:enable Scriptbaker_ShipOutOfStockItems
   ```

3. **Run Setup Upgrade:**
   ```sh
   bin/magento setup:upgrade
   ```

4. **Clear Cache:**
   ```sh
   bin/magento cache:clean
   ```

## Usage

Once the module is installed and enabled, it will automatically adjust stock quantities to allow for the creation of shipments for out-of-stock items. No additional configuration is necessary.

## Implementation Details

The module listens to two key events and adjusts the stock quantities accordingly:

1. **`sales_order_shipment_save_before`:**
   - Adjusts stock quantities before the shipment is created to ensure it can be processed without errors.
2. **`sales_order_shipment_save_after`:**
   - Updates stock quantities after the shipment is saved to reflect the accurate inventory levels.

## Files Included

- `registration.php`: Registers the module with Magento.
- `etc/module.xml`: Declares the module and its version.
- `etc/events.xml`: Registers the observers for the `sales_order_shipment_save_before` and `sales_order_shipment_save_after` events.
- `Observer/AlwaysShipOrder.php`: Contains the logic for adjusting stock quantities before shipment creation.
- `Observer/UpdateStockAfterShipment.php`: Contains the logic for updating stock quantities after the shipment is saved.

## Observer Logic

### `AlwaysShipOrder`

The `AlwaysShipOrder` observer performs the following actions:

1. Retrieves all items in the shipment.
2. For each item, checks the available stock quantity.
3. If the stock quantity is less than the requested shipment quantity, it adjusts the stock quantity to meet the shipment requirement.
4. Saves the updated stock quantity using the `SourceItemsSaveInterface`.

### `UpdateStockAfterShipment`

The `UpdateStockAfterShipment` observer performs the following actions:

1. Retrieves all items in the shipment.
2. Updates the stock quantities after the shipment is saved to reflect accurate inventory levels.
3. Ensures that the inventory system is up-to-date with the changes caused by the shipment.

## Contributing

If you wish to contribute to this module, please fork the repository and submit a pull request with your changes. All contributions are welcome.

## Support

If you encounter any issues or have any questions regarding the module, please open an issue on the repository or contact us at [hello@scriptbaker.com].

## License

This module is open-source and licensed under the MIT License.

---

Thank you for using `Scriptbaker_ShipOutOfStockItems`. We hope it helps improve your Magento 2 experience.