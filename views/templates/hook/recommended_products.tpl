<h1>Hola esto es un ejemplo</h1>
{if $products}
    <ul>
        {foreach from=$products item=product}
            <li>
                <a href="{$product.link|escape:'html':'UTF-8'}">
                    <img src="{$product.cover.bySize.home_default.url|escape:'html':'UTF-8'}" alt="{$product.name|escape:'html':'UTF-8'}">
                    <h3>{$product.name|escape:'html':'UTF-8'}</h3>
                </a>
            </li>
        {/foreach}
    </ul>
{else}
    <p>No hay productos recomendados</p>
{/if}