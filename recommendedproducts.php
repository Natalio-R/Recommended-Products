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
        $this->ps_versions_compliancy = [
            'min' => '1.7.1.0',
            'max' => _PS_VERSION_,
        ];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Recommended products');
        $this->description = $this->l('Show featured products on the home page of your online store');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
    }

    public function install()
    {
        $this->_clearCache('*');

        return parent::install() && $this->registerHook('displayHome') && $this->registerHook('displayRecommendedProducts');
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
        // Obtenga el ID del producto a eliminar
        $id_product = (int) Tools::getValue('id_product');

        // Obtenga la lista de productos seleccionados del valor de configuración
        $selectedProducts = Configuration::get('RECOMMENDEDPRODUCTS');
        $selectedProducts = explode(',', $selectedProducts);

        // Encuentre el índice del producto a eliminar
        $index = array_search($id_product, $selectedProducts);
        if ($index !== false) {
            // Elimine el producto de la lista de productos seleccionados
            unset($selectedProducts[$index]);

            // Guarde la lista actualizada de productos seleccionados
            Configuration::updateValue('RECOMMENDEDPRODUCTS', implode(',', $selectedProducts));
        }

        // Redirigir a la página de configuración del módulo
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
                'class' => 'fixed-width-sm',
                'type' => 'bool',
                'float' => true, // a trick - prevents from html escaping
                'orderby' => false,
                'search' => false,
            ),

        );

        $helper = new HelperList();
        $helper->shopLinkType = '';
        $helper->simple_header = true;
        // $helper->actions = array('deleteProduct');
        // $helper->actions = array(
        //     'delete' => array(
        //         'text' => $this->l('Delete'),
        //         'confirm' => $this->l('Are you sure you want to delete the selected items?'),
        //         'icon' => 'icon-trash',
        //         'callback' => array('delete_product'),
        //     )
        // );
        $helper->identifier = 'id_product';
        $helper->table = 'product';
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $selectedProducts = $products;
        $products = Product::getProducts(Context::getContext()->language->id, 0, 0, 'id_product', 'ASC');
        $productOptions = array();

        foreach ($products as $product) {
            if (in_array($product['id_product'], $selectedProducts)) {
                // $deleteUrl = $this->context->link->getAdminLink('AdminModuleName', true) . '&id_product=' . $product['id_product'] . '&delete_product';
                // $deleteButton = '<a href="' . $deleteUrl . '" class="delete-button">' . $this->l('Delete') . '</a>';

                $delete_link = $this->context->link->getAdminLink('AdminModules') . '&configure=' . $this->name . '&delete_product=' . $product['id_product'] . '&token=' . Tools::getAdminTokenLite('AdminModules');
                $deleteButton = '<a href="' . $delete_link . '">Delete</a>';


                $productOptions[] = array(
                    'id_product' => $product['id_product'],
                    'name' => $product['name'],
                    'price' => $product['price'],
                    'delete_link' => $deleteButton
                );
            }
        }

        if (Tools::isSubmit('delete_product')) {
            $this->processDeleteProduct();
            // Tools::redirectAdmin($this->context->link->getAdminLink('AdminProducts') . '&configure=' . $this->name . '&token=' . Tools::getAdminTokenLite('AdminModules'));
        }

        $helper->listTotal = count($productOptions);
        $helper->_pagination = array(20, 50, 100, 300);
        // $helper->bulk_actions = array(
        //     'delete' => array(
        //         'text' => $this->l('Delete selected'),
        //         'confirm' => $this->l('Are you sure you want to delete the selected items?'),
        //         'icon' => 'icon-trash',
        //     )
        // );

        return $helper->generateList($productOptions, $fields_list);
    }

    public function processDeleteProduct()
    {
        // Obtenga el ID del producto a eliminar
        $id_product = (int) Tools::getValue('delete_product');

        // Obtenga la lista de productos seleccionados del valor de configuración
        $selectedProducts = Configuration::get('RECOMMENDEDPRODUCTS');

        $selectedProducts = explode(',', $selectedProducts);

        // Encuentre el índice del producto a eliminar
        $index = array_search($id_product, $selectedProducts);
        if ($index !== false) {
            // Elimine el producto de la lista de productos seleccionados
            unset($selectedProducts[$index]);

            // Guarde la lista actualizada de productos seleccionados
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
            if (Validate::isLoadedObject($product)) {
                $products[] = $product;
            }
        }

        $this->context->smarty->assign(array(
            'products' => $products,
        ));

        return $this->display(__FILE__, 'recommendedproducts.tpl');
    }
}
