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
                <i class="fa fa-search"></i> Vista rápida
              </a>  
            </div>
          </div>
          <div class="product-description">
            <h3 class="h3 product-title">
              <a href="#">
                {$product->name[1]}
              </a>
            </h3>
            <div class="product-price-and-shipping">
              <span class="price" ariea-label="precio">{$product->price}€</span>
            </div>
            <div class="card-footer text-center">
              <a href="{$product->link}" class="btn btn-primary">Ver producto</a>
            </div>
          </div>
        </div>
      </article>
    </div>
    {/foreach}
  </div>
{else}
  <p>No hay productos disponibles</p>
{/if}
</section>