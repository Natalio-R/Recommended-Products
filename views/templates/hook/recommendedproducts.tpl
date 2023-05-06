<section class="featured-products clearfix mt-3">
<h2 class="h2 products-section-title text-uppercase">{l s='Recommended products' d='Modules.Recommendedproducts.Shop'}</h2>
{if $products}
  <div class="products">
    {foreach $products as $product}
    <div class="js-product product col-xs-12 col-sm-6 col-lg-4 col-xl-3">
      <article class="product-miniature js-product-miniature">
        <div class="thumbnail-container">
          <div class="thumbnail-top">
            <a href="#" class="thumbnail product-thumbnail">
              <img src="{$product->id|cat:'-large_default/'|cat:$product->link_rewrite[1]|cat:'.jpg'}" class="card-img-top" alt="{$product->name[1]}" width="250" height="250">
            </a>
            <div class="highlighted-informations no-variants">
              <a class="quick-view js-quick-view" href="#" data-link-action="quickview">
                <i class="material-icons search">&#xE8B6;</i> {l s='Quick view' d='Shop.Theme.Actions'}
              </a>  
            </div>
          </div>
          <div class="product-description">
            <h3 class="h3 product-title">
              <a href="{$product->url}">
                {$product->name[1]}
              </a>
            </h3>
            <div class="product-price-and-shipping">
              <span class="price" ariea-label="precio">
                {number_format($product->price, 2, ',', '.')} €
              </span>
            </div>
          </div>
          <ul class="product-flags js-product-flags">
          {foreach $product->condition as $flag}
            <li class="product-flag {$flag}">{$flag}</li>
        {/foreach}
          </ul>
        </div>
      </article>
    </div>
    {/foreach}
  </div>
{else}
  <p>No hay productos disponibles</p>
{/if}
</section>
