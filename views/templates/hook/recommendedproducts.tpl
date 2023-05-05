
  <ul class="product-list">
    {foreach from=$products item=product}
      <li>
        <h3>{$product.name}</h3>
        <p>{$product.description_short}</p>
        <p>Precio: {$product.price|displayPrice}</p>
        <a href="{$product.link}" class="btn btn-default">Ver producto</a>
      </li>
    {/foreach}
  </ul>

  <section>
  <h1>{l s='Our Products' d='Modules.Featuredproducts.Shop'}</h1>
  <div class="products">
    {foreach from=$products item="product"}
      {include file="catalog/_partials/miniatures/product.tpl" product=$product}
    {/foreach}
  </div>
  <a href="{$product.link}">{l s='All products' d='Modules.Featuredproducts.Shop'}</a>
</section>