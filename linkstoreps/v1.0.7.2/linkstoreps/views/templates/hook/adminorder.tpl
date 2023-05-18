{**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 *}

{**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 *}
<div class="card mt-2" id="view_order_payments_block">
<div class="card-header">
    <h3 class="card-header-title">
      Productos Sumary
    </h3>
  </div>
  
  <div class="card-body">
    <div class="spinner-order-products-container" id="orderProductsLoading">
      <div class="spinner spinner-primary"></div>
    </div>
        
    <table class="table" id="orderProductsTable" data-currency-precision="0">
      <thead>
      <tr>
        <th>
          <p>Supplier</p>
        </th>
        <th>
          <p>Producto</p>
        </th>
        <th>
          <p>SKU</p>
        </th>
        <th>
          <p>In Stock</p>
        </th>
      </tr>
      </thead>
      <tbody>
      
  {foreach from=$pidcod item=ids name=info} 
  
<tr id="orderProduct_31966" class="cellProduct">

    <td class="cellProductName">
      <p class="mb-0 productName">{$ids.supplier}</p>
    </td>
    <td class="cellProductName">
      <p class="mb-0 productName">
          {$ids.name}
      </p>
    </td>
    <td class="cellProductName">
        <p class="mb-0 productReference">
          {$ids.reference}
        </p>
    </td>
    <td class="cellProductName">
        <p class="mb-0 productReference" style="{if $ids.product_quantity <= 10}color:red{else}color:blue{/if}">
          {$ids.product_quantity}{if $ids.0.overstock == 1} Stock Compratecno{/if} 
        </p>
    </td>
</tr>
    {/foreach}
    </table>
      </thead>
</div>
</div>