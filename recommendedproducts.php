<?php

/**
 * 
 * @author    Natalio Rabasco <nataliorabasconavaro@gmail.com>
 * @copyright 2023 Natalio Rabasco
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * 
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class RecommendedProducts extends Module
{
    public function __construct()
    {
        $this->name = 'recommendedproducts';
        $this->tab = 'front_office_features';
        $this->author = 'Natalio Rabasco';
        $this->version = '1.0.0';
        $this->need_instance = 0;
        $this->bootstrap = true;
        $this->ps_versions_compliancy = [
            'min' => '1.7.1.0',
            'max' => _PS_VERSION_,
        ];

        parent::__construct();

        $this->displayName = $this->l('Recommended products');
        $this->description = $this->l('Show featured products on the home page of your online store');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
    }

    public function install()
    {
        $this->_clearCache('*');

        return parent::install()
            && $this->registerHook('displayHome')
            && $this->registerHook('displayRecommendedProducts');
    }

    public function uninstall()
    {
        $this->_clearCache('*');

        if (!parent::uninstall() || $this->unregisterHook('displayRecommendedProducts')) {
            return false;
        }

        return true;
    }

    public function getContent()
    {
        $html = '';

        if (Tools::isSubmit('submitAddconfiguration')) {
            $selectedProducts = Tools::getValue('recommendedproducts');

            if (!is_array($selectedProducts)) {
                $selectedProducts = array($selectedProducts);
            }

            $existingProducts = explode(',', Configuration::get('RECOMMENDEDPRODUCTS'));
            $selectedProducts = array_unique(array_merge($existingProducts, $selectedProducts));

            Configuration::updateValue('RECOMMENDEDPRODUCTS', implode(',', $selectedProducts));
            $html .= $this->displayConfirmation($this->l('The selected products have been updated.'));
        }

        $products = Product::getProducts(Context::getContext()->language->id, 0, 0, 'name', 'ASC');
        $selectedProducts = explode(',', Configuration::get('RECOMMENDEDPRODUCTS'));

        $productOptions = array();
        foreach ($products as $product) {
            $productOptions[] = array(
                'id' => $product['id_product'],
                'name' => $product['name'],
            );
        }

        $form = $this->generateForm($productOptions);
        $list = $this->renderList($selectedProducts);
        $html .= $form . $list;

        return $html;
    }

    protected function generateForm($productOptions)
    {
        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->default_form_language = (int)Configuration::get('PS_LANG_DEFAULT');
        $helper->allow_employee_form_lang = (int)Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG');
        $helper->title = $this->displayName;

        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Recommended products'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'select',
                        'label' => $this->l('Select products'),
                        'name' => 'recommendedproducts[]',
                        'options' => array(
                            'query' => $productOptions,
                            'id' => 'id',
                            'name' => 'name'
                        ),
                        'class' => 'w-full',
                        'multiple' => true,
                        'desc' => $this->l('Select the products you want to display on the home page.'),
                        'size' => 10,
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                    'class' => 'btn btn-default pull-right',
                ),
            ),
        );

        $helper->fields_value['recommendedproducts'] = explode(',', Configuration::get('RECOMMENDEDPRODUCTS'));

        return $helper->generateForm(array($fields_form));
    }

    public function processMyDelete()
    {
        $id_product = (int) Tools::getValue('id_product');
        $selectedProducts = Configuration::get('RECOMMENDEDPRODUCTS');
        $selectedProducts = explode(',', $selectedProducts);
        $index = array_search($id_product, $selectedProducts);

        if ($index !== false) {
            unset($selectedProducts[$index]);

            Configuration::updateValue('RECOMMENDEDPRODUCTS', implode(',', $selectedProducts));
        }

        Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules') . '&configure=' . $this->name . '&token=' . Tools::getAdminTokenLite('AdminModules'));
    }


    public function renderList($products)
    {
        $fields_list = array(
            'id_product' => array(
                'title' => $this->l('ID'),
                'align' => 'center',
                'class' => 'fixed-width-xs'
            ),
            'name' => array(
                'title' => $this->l('Name'),
            ),
            'price' => array(
                'title' => $this->l('Price'),
            ),
            'delete_link' => array(
                'title' => $this->l('Delete'),
                'align' => 'right',
                'class' => 'fixed-width-md',
                'type' => 'bool',
                'float' => true,
                //'orderby' => false,
                //'search' => false,
            ),
        );

        $helper = new HelperList();
        $helper->shopLinkType = '';
        $helper->simple_header = true;
        $helper->identifier = 'id_product';
        $helper->table = 'product';
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $selectedProducts = $products;
        $products = Product::getProducts(Context::getContext()->language->id, 0, 0, 'id_product', 'ASC');
        $productOptions = array();

        foreach ($products as $product) {
            if (in_array($product['id_product'], $selectedProducts)) {
                $delete_link = $this->context->link->getAdminLink('AdminModules') . '&configure=' . $this->name . '&delete_product=' . $product['id_product'] . '&token=' . Tools::getAdminTokenLite('AdminModules');
                $deleteButton = '<a href="' . $delete_link . '" class="btn btn-default"><i class="icon-trash"></i>&nbsp;Delete</a>';
                $price = Product::getPriceStatic($product['id_product'], true);

                $productOptions[] = array(
                    'id_product' => $product['id_product'],
                    'name' => $product['name'],
                    'price' => $this->printPrice($price, 'price') . ' €',
                    'delete_link' => $deleteButton,
                );
            }
        }

        if (Tools::isSubmit('delete_product')) {
            $this->processDeleteProduct();
        }

        $helper->listTotal = count($productOptions);
        $helper->_pagination = array(20, 50, 100, 300);

        return $helper->generateList($productOptions, $fields_list);
    }

    protected function printPrice($value, $row)
    {
        return number_format((float)$value, 2, ',', '');
    }

    public function processDeleteProduct()
    {
        $id_product = (int) Tools::getValue('delete_product');
        $selectedProducts = Configuration::get('RECOMMENDEDPRODUCTS');
        $selectedProducts = explode(',', $selectedProducts);
        $index = array_search($id_product, $selectedProducts);

        if ($index !== false) {
            unset($selectedProducts[$index]);

            Configuration::updateValue('RECOMMENDEDPRODUCTS', implode(',', $selectedProducts));
        }

        Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules') . '&configure=' . $this->name . '&token=' . Tools::getAdminTokenLite('AdminModules'));
    }

    public function hookDisplayRecommendedProducts($params)
    {
        $selectedProducts = Configuration::get('RECOMMENDEDPRODUCTS');
        $productIds = explode(',', $selectedProducts);
        $products = array();

        foreach ($productIds as $productId) {
            $product = new Product($productId);
            $image = Image::getCover($product->id);
            $imageUrl = $this->context->link->getImageLink($product->link_rewrite, $image['id_image'], ImageType::getFormattedName('home'));
            $productLink = $this->context->link->getProductLink($product);

            if (Validate::isLoadedObject($product)) {
                $basePrice = $product->getPrice(false);
                $offerPrice = $product->getPrice(true);
                $products[] = array(
                    'id' => $product->id,
                    'name' => $product->name,
                    'description_short' => $product->description_short,
                    'description' => $product->description,
                    'link_rewrite' => $product->link_rewrite,
                    'price' => $product->price,
                    'quantity' => 1,
                    'image' => $imageUrl,
                    'product_link' => $productLink,
                    'category' => $product->category,
                    'manufacturer' => $product->manufacturer_name,
                    'base_price' => $basePrice,
                    'offer_price' => $offerPrice,
                );
            }
        }

        $this->context->smarty->assign(array(
            'products' => $products,
        ));


        $this->context->smarty->assign('basePrice', $basePrice);
        $this->context->smarty->assign('offerPrice', $offerPrice);

        //var_dump($this->regular_price);
        return $this->display(__FILE__, 'recommendedproducts.tpl');
    }
}
